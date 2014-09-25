<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       modulesconfig
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * Class Admin_oxpsModulesConfigDashboardTest
 * Tests for admin controller Admin_oxpsModulesConfigDashboard.
 *
 * @see Admin_oxpsModulesConfigDashboard
 */
class Admin_oxpsModulesConfigDashboardTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var Admin_oxpsModulesConfigDashboard
     */
    protected $SUT;


    /**
     * Set SUT state before test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock(
            'Admin_oxpsModulesConfigDashboard',
            array('__construct', '__call', 'init', 'render', '_isReadableFile')
        );
    }

    /**
     * Data provider for invalid import file data tests
     *
     * @return array Keys: File data | Json validator errors | Tmp file path | Is file readable | Expected errors
     */
    public function  invalidImportFileDataProvider()
    {
        return array(

            // Empty file data
            array(
                array(),
                array('JSON_VALIDATOR_ERR'),
                '',
                false,
                array(
                    'OXPS_MODULESCONFIG_ERR_NO_FILE',
                    'OXPS_MODULESCONFIG_ERR_FILE_TYPE',
                    'OXPS_MODULESCONFIG_ERR_CANNOT_READ',
                    'JSON_VALIDATOR_ERR'
                )
            ),

            // File upload error
            array(
                array(
                    'error'    => UPLOAD_ERR_PARTIAL,
                    'type'     => 'application/octet-stream',
                    'tmp_name' => '/path/to/file.json'
                ),
                array(),
                '/path/to/file.json',
                true,
                array('OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR')
            ),

            // Invalid file type
            array(
                array(
                    'error'    => '',
                    'type'     => 'application/pdf',
                    'tmp_name' => '/path/to/file.pdf'
                ),
                array(),
                '/path/to/file.pdf',
                true,
                array('OXPS_MODULESCONFIG_ERR_FILE_TYPE')
            ),

            // File is not readable
            array(
                array(
                    'error'    => '',
                    'type'     => 'application/json',
                    'tmp_name' => '/path/to/file.json'
                ),
                array(),
                '/path/to/file.json',
                false,
                array('OXPS_MODULESCONFIG_ERR_CANNOT_READ')
            ),

            // File content validation errors
            array(
                array(
                    'error'    => '',
                    'type'     => 'application/json',
                    'tmp_name' => '/path/to/file.json'
                ),
                array('ERR_JSON_CORRUPT'),
                '/path/to/file.json',
                true,
                array('ERR_JSON_CORRUPT')
            ),
        );
    }


    public function testGetModulesList()
    {
        $this->_setConfigAndModulesListMocks();

        $this->assertEquals(
            array(
                'my_module'      => (object) array('version' => '1.0.0'),
                'good_extension' => (object) array('version' => '0.2.5'),
            ),
            $this->SUT->getModulesList()
        );
    }


    public function testGetSettingsList()
    {
        $mSettings = $this->SUT->getSettingsList();

        $this->assertTrue( is_array( $mSettings ) );
        $this->assertArrayHasKey( 'version', $mSettings );
        $this->assertArrayHasKey( 'extend', $mSettings );
        $this->assertArrayHasKey( 'files', $mSettings );
        $this->assertArrayHasKey( 'templates', $mSettings );
        $this->assertArrayHasKey( 'blocks', $mSettings );
        $this->assertArrayHasKey( 'settings', $mSettings );
        $this->assertArrayHasKey( 'events', $mSettings );
    }


    public function testGetAction_nothingSet_returnEmptyString()
    {
        $this->assertSame( '', $this->SUT->getAction() );
    }

    public function testGetAction_actionNameSet_returnTheValue()
    {
        $this->SUT->setAction( 'import' );

        $this->assertSame( 'import', $this->SUT->getAction() );
    }


    public function testGetErrors_nothingSet_returnEmptyArray()
    {
        $this->assertSame( array(), $this->SUT->getErrors() );
    }

    public function testGetErrors_errorsAdded_returnTheErrorsAsArray()
    {
        $this->SUT->addError( 'ERR' );
        $this->SUT->addError( 'FATAL_ERR' );

        $this->assertSame( array('ERR', 'FATAL_ERR'), $this->SUT->getErrors() );
    }

    public function testGetErrors_multipleErrorsAdded_returnTheErrorsAsArray()
    {
        $this->SUT->addError( 'ERR' );
        $this->SUT->addError( 'FATAL_ERR' );

        $this->SUT->addErrors( array('ERR2', 'ERR3') );

        $this->assertSame( array('ERR', 'FATAL_ERR', 'ERR2', 'ERR3'), $this->SUT->getErrors() );
    }

    public function testGetErrors_errorsReset_returnEmptyArray()
    {
        $this->SUT->addError( 'ERR' );
        $this->SUT->addError( 'FATAL_ERR' );

        $this->SUT->addErrors( array('ERR2', 'ERR3') );

        $this->SUT->resetError();

        $this->assertSame( array(), $this->SUT->getErrors() );
    }


    public function testGetMessages_nothingSet_returnEmptyArray()
    {
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testGetMessages_messagesAdded_returnTheMessagesAsArray()
    {
        $this->SUT->addMessage( 'MSG' );

        $this->assertSame( array('MSG'), $this->SUT->getMessages() );
    }


    public function testActionSubmit_noRequestData_setErrors()
    {
        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( '', $this->SUT->getAction() );
        $this->assertSame(
            array(
                'OXPS_MODULESCONFIG_ERR_NO_MODULES',
                'OXPS_MODULESCONFIG_ERR_NO_SETTINGS',
                'OXPS_MODULESCONFIG_ERR_INVALID_ACTION'
            ),
            $this->SUT->getErrors()
        );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_invalidActionRequested_setError()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_resetall', 1 );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( '', $this->SUT->getAction() );
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_INVALID_ACTION'), $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_moModulesRequested_setError()
    {
        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array() );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_export', 1 );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'export', $this->SUT->getAction() );
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_NO_MODULES'), $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_invalidModuleRequested_setError()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('oxpsmodulesconfig') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_export', 1 );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'export', $this->SUT->getAction() );
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_INVALID_MODULE'), $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_noSettingsRequested_setError()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array() );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_export', 1 );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'export', $this->SUT->getAction() );
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_NO_SETTINGS'), $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_invalidSettingRequested_setError()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'picture' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_export', 1 );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'export', $this->SUT->getAction() );
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_INVALID_SETTING'), $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_validExportRequest_callExportAndDownloadHandler()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_export', 1 );

        // Configuration data transfer handler mock
        $oConfigTransfer = $this->getMock( 'oxpsModulesConfigTransfer', array('__call', 'exportForDownload') );
        $oConfigTransfer->expects( $this->once() )->method( 'exportForDownload' )->with(
            array(
                'modules'  => array('my_module'),
                'settings' => array('version', 'extend'),
                'action'   => 'export'
            )
        );

        oxTestModules::addModuleObject( 'oxpsModulesConfigTransfer', $oConfigTransfer );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'export', $this->SUT->getAction() );
        $this->assertSame(
            array('OXPS_MODULESCONFIG_ERR_EXPORT_FAILED'),
            $this->SUT->getErrors(),
            'Error should be set for case when download fails.'
        );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_validBackupRequest_callExportAndSaveToFileHandler()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module', 'good_extension') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_backup', 1 );

        // Configuration data transfer handler mock
        $oConfigTransfer = $this->getMock( 'oxpsModulesConfigTransfer', array('__call', 'backupToFile') );
        $oConfigTransfer->expects( $this->once() )->method( 'backupToFile' )->with(
            array(
                'modules'  => array('my_module', 'good_extension'),
                'settings' => array('version', 'extend'),
                'action'   => 'backup'
            ),
            ''
        )->will( $this->returnValue( 888 ) );

        oxTestModules::addModuleObject( 'oxpsModulesConfigTransfer', $oConfigTransfer );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'backup', $this->SUT->getAction() );
        $this->assertSame( array(), $this->SUT->getErrors() );
        $this->assertSame( array('OXPS_MODULESCONFIG_MSG_BACKUP_SUCCESS'), $this->SUT->getMessages() );
    }

    public function testActionSubmit_backupFailedToSaveFile_setBackupError()
    {
        $this->_setConfigAndModulesListMocks();

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('good_extension') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'files' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_backup', 1 );

        // Configuration data transfer handler mock
        $oConfigTransfer = $this->getMock( 'oxpsModulesConfigTransfer', array('__call', 'backupToFile') );
        $oConfigTransfer->expects( $this->once() )->method( 'backupToFile' )->with(
            array(
                'modules'  => array('good_extension'),
                'settings' => array('version', 'files'),
                'action'   => 'backup'
            ),
            ''
        )->will( $this->returnValue( 0 ) );

        oxTestModules::addModuleObject( 'oxpsModulesConfigTransfer', $oConfigTransfer );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'backup', $this->SUT->getAction() );
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_BACKUP_FAILED'), $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    /**
     * @dataProvider invalidImportFileDataProvider
     */
    public function testActionSubmit_importErrors( array $aFileData, array $aValidatorErrors, $sFilePath,
                                                   $blFileReadable, array $aExpectedErrors )
    {
        $this->_setConfigAndModulesListMocks( $aFileData );

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_import', 1 );

        // Configuration data transfer handler mock
        $oConfigTransfer = $this->getMock(
            'oxpsModulesConfigTransfer',
            array('__call', 'setImportDataFromFile', 'getImportDataValidationErrors')
        );
        $oConfigTransfer->expects( $this->once() )->method( 'setImportDataFromFile' )->with( $aFileData );
        $oConfigTransfer->expects( $this->once() )->method( 'getImportDataValidationErrors' )->will(
            $this->returnValue( $aValidatorErrors )
        );

        oxTestModules::addModuleObject( 'oxpsModulesConfigTransfer', $oConfigTransfer );

        if ( !empty( $sFilePath ) ) {
            $this->SUT->expects( $this->once() )->method( '_isReadableFile' )->with( $sFilePath )->will(
                $this->returnValue( (bool) $blFileReadable )
            );
        }

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'import', $this->SUT->getAction() );
        $this->assertSame( $aExpectedErrors, $this->SUT->getErrors() );
        $this->assertSame( array(), $this->SUT->getMessages() );
    }

    public function testActionSubmit_importSuccess()
    {
        $this->_setConfigAndModulesListMocks(
            array(
                'error'    => '',
                'type'     => 'application/json',
                'tmp_name' => '/path/to/file.json'
            )
        );

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('my_module') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_import', 1 );

        // Configuration data transfer handler mock
        $oConfigTransfer = $this->getMock(
            'oxpsModulesConfigTransfer',
            array(
                '__call', 'setImportDataFromFile', 'getImportDataValidationErrors', 'backupToFile',
                'importData', 'getImportErrors'
            )
        );
        $oConfigTransfer->expects( $this->exactly( 2 ) )->method( 'setImportDataFromFile' )->with(
            array(
                'error'    => '',
                'type'     => 'application/json',
                'tmp_name' => '/path/to/file.json'
            )
        );
        $oConfigTransfer->expects( $this->once() )->method( 'getImportDataValidationErrors' )->will(
            $this->returnValue( array() )
        );
        $oConfigTransfer->expects( $this->once() )->method( 'backupToFile' )->with(
            array(
                'modules'  => array('my_module', 'good_extension'),
                'settings' => array('version', 'extend', 'files', 'templates', 'blocks', 'settings', 'events'),
                'action'   => ''
            ),
            'full_backup'
        )->will( $this->returnValue( true ) );
        $oConfigTransfer->expects( $this->once() )->method( 'importData' )->with(
            array(
                'modules'  => array('my_module'),
                'settings' => array('version', 'extend'),
                'action'   => 'import'
            )
        )->will( $this->returnValue( true ) );
        $oConfigTransfer->expects( $this->never() )->method( 'getImportErrors' );

        oxTestModules::addModuleObject( 'oxpsModulesConfigTransfer', $oConfigTransfer );

        // Module utils mock
        $oModule = $this->getMock( 'oxpsModulesConfigModule', array('cleanTmp') );
        //$oModule->expects( $this->once() )->method( 'cleanTmp' ); // TODO: not working!

        oxTestModules::addModuleObject( 'oxpsModulesConfigModule', $oModule );

        $this->SUT->expects( $this->once() )->method( '_isReadableFile' )->with( '/path/to/file.json' )->will(
            $this->returnValue( true )
        );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'import', $this->SUT->getAction() );
        $this->assertSame( array(), $this->SUT->getErrors() );
        $this->assertSame(
            array(
                'OXPS_MODULESCONFIG_MSG_BACKUP_SUCCESS',
                'OXPS_MODULESCONFIG_MSG_IMPORT_SUCCESS'
            ),
            $this->SUT->getMessages()
        );
    }

    public function testActionSubmit_importFailure()
    {
        $this->_setConfigAndModulesListMocks(
            array(
                'error'    => '',
                'type'     => 'application/json',
                'tmp_name' => '/path/to/file.json'
            )
        );

        modConfig::setRequestParameter( 'oxpsmodulesconfig_modules', array('good_extension') );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_settings', array('version' => 1, 'extend' => 1) );
        modConfig::setRequestParameter( 'oxpsmodulesconfig_import', 1 );

        // Configuration data transfer handler mock
        $oConfigTransfer = $this->getMock(
            'oxpsModulesConfigTransfer',
            array(
                '__call', 'setImportDataFromFile', 'getImportDataValidationErrors', 'backupToFile',
                'importData', 'getImportErrors'
            )
        );
        $oConfigTransfer->expects( $this->exactly( 2 ) )->method( 'setImportDataFromFile' )->with(
            array(
                'error'    => '',
                'type'     => 'application/json',
                'tmp_name' => '/path/to/file.json'
            )
        );
        $oConfigTransfer->expects( $this->once() )->method( 'getImportDataValidationErrors' )->will(
            $this->returnValue( array() )
        );
        $oConfigTransfer->expects( $this->once() )->method( 'backupToFile' )->with(
            array(
                'modules'  => array('my_module', 'good_extension'),
                'settings' => array('version', 'extend', 'files', 'templates', 'blocks', 'settings', 'events'),
                'action'   => ''
            ),
            'full_backup'
        )->will( $this->returnValue( true ) );
        $oConfigTransfer->expects( $this->once() )->method( 'importData' )->with(
            array(
                'modules'  => array('good_extension'),
                'settings' => array('version', 'extend'),
                'action'   => 'import'
            )
        )->will( $this->returnValue( false ) );
        $oConfigTransfer->expects( $this->once() )->method( 'getImportErrors' )->will(
            $this->returnValue( array('ERR_IMPORT_FAILURE') )
        );

        oxTestModules::addModuleObject( 'oxpsModulesConfigTransfer', $oConfigTransfer );

        // Module utils mock
        $oModule = $this->getMock( 'oxpsModulesConfigModule', array('cleanTmp') );
        $oModule->expects( $this->never() )->method( 'cleanTmp' );

        oxTestModules::addModuleObject( 'oxpsModulesConfigModule', $oModule );

        $this->SUT->expects( $this->once() )->method( '_isReadableFile' )->with( '/path/to/file.json' )->will(
            $this->returnValue( true )
        );

        $this->assertFalse( $this->SUT->actionSubmit() );

        $this->assertSame( 'import', $this->SUT->getAction() );
        $this->assertSame( array('ERR_IMPORT_FAILURE'), $this->SUT->getErrors() );
        $this->assertSame( array('OXPS_MODULESCONFIG_MSG_BACKUP_SUCCESS'), $this->SUT->getMessages() );
    }


    /**
     * Set oxConfig mock and oxModulesList mock.
     *
     * @param null|array $mFileData File data mock for import.
     */
    protected function _setConfigAndModulesListMocks( $mFileData = null )
    {
        // Config mock
        $oConfig = $this->getMock( 'oxConfig', array('getModulesDir', 'getUploadedFile') );
        $oConfig->expects( $this->any() )->method( 'getModulesDir' )->will( $this->returnValue( '/shop/modules/' ) );

        if ( is_array( $mFileData ) ) {
            $oConfig->expects( $this->once() )->method( 'getUploadedFile' )->with( 'oxpsmodulesconfig_file' )->will(
                $this->returnValue( $mFileData )
            );
        }

        oxRegistry::set( 'oxConfig', $oConfig );

        // Modules list mock
        $oModuleList = $this->getMock( 'oxModuleList', array('__call', 'getModulesFromDir') );
        $oModuleList->expects( $this->any() )->method( 'getModulesFromDir' )->with( '/shop/modules/' )->will(
            $this->returnValue(
                array(
                    'my_module'         => (object) array('version' => '1.0.0'),
                    'oxpsmodulesconfig' => (object) array('version' => '0.1.0'),
                    'good_extension'    => (object) array('version' => '0.2.5'),
                )
            )
        );

        oxTestModules::addModuleObject( 'oxModuleList', $oModuleList );
    }
}

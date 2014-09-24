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
 * Class oxpsModulesConfigTransferTest
 * Tests for core class oxpsModulesConfigTransfer.
 *
 * @see oxpsModulesConfigTransfer
 */
class oxpsModulesConfigTransferTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var oxpsModulesConfigTransfer
     */
    protected $SUT;


    /**
     * Set SUT state before test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock( 'oxpsModulesConfigTransfer', array('__call', 'getConfig', '_jsonDownload') );
    }

    /**
     * Data provider for request and export data.
     *
     * @return array
     */
    public function exportDataProvider()
    {
        return array(
            array(array(), array()),
            array(array('modules' => array(), 'settings' => array('version')), array()),
            array(array('modules' => array('mymodule'), 'settings' => array()), array('mymodule' => array())),
            array(
                array('modules' => array('mymodule'), 'settings' => array('version')),
                array('mymodule' => array('version' => '_SETTING_'))
            ),
            array(
                array(
                    'modules'  => array('mymodule', 'othermodule'),
                    'settings' => array('version', 'extend', 'files')
                ),
                array(
                    'mymodule'    => array('version' => '_SETTING_', 'extend' => '_SETTING_', 'files' => '_SETTING_'),
                    'othermodule' => array('version' => '_SETTING_', 'extend' => '_SETTING_', 'files' => '_SETTING_'),
                )
            ),
        );
    }

    /**
     * @dataProvider exportDataProvider
     */
    public function testExportForDownload( array $aRequestData, array $aExpectedModulesExportData )
    {
        // Config mock
        $oConfig = $this->getMock( 'oxConfig', array('getVersion', 'getEdition', 'getShopId') );
        $oConfig->expects( $this->once() )->method( 'getVersion' )->will( $this->returnValue( '5.2.0' ) );
        $oConfig->expects( $this->once() )->method( 'getEdition' )->will( $this->returnValue( 'PE' ) );
        $oConfig->expects( $this->once() )->method( 'getShopId' )->will( $this->returnValue( 2 ) );

        // Configuration storage mock
        $oConfigStorage = $this->getMock( 'oxpsModulesConfigStorage', array('__call', 'load') );
        $oConfigStorage->expects( $this->any() )->method( 'load' )->will( $this->returnValue( '_SETTING_' ) );

        oxTestModules::addModuleObject( 'oxpsModulesConfigStorage', $oConfigStorage );

        $this->SUT->expects( $this->once() )->method( 'getConfig' )->will( $this->returnValue( $oConfig ) );
        $this->SUT->expects( $this->once() )->method( '_jsonDownload' )->with(
            $this->stringEndsWith( '.json' ),
            $this->equalTo(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion' => '5.2.0',
                        'sShopEdition' => 'PE',
                        'sShopId'      => 2,
                        'aModules'     => $aExpectedModulesExportData,
                    )
                )
            )
        );

        $this->SUT->exportForDownload( $aRequestData );
    }

    //todo ddr: positive tests with better configuration Configuration storage; other methods tests
}

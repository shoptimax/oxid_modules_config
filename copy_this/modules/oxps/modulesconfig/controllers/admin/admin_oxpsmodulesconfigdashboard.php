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
 * Class Admin_oxpsModulesConfigDashboard.
 * Modules configuration export, backup and import tools controller.
 */
class Admin_oxpsModulesConfigDashboard extends oxAdminView
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'admin_oxpsmodulesconfigdashboard.tpl';

    /**
     * List of modules to exclude from export and import.
     *
     * @var array
     */
    protected $_aExcludeModules = array('oxpsmodulesconfig');

    /**
     * List of module related configuration parameters to export or import.
     * These are parts of module metadata which are registered stored in database.
     *
     * @var array
     */
    protected $_aModuleSettings = array('version', 'extend', 'files', 'templates', 'blocks', 'settings', 'events');

    /**
     * Current action name.
     * Supported actions are: export, backup and import.
     *
     * @var string
     */
    protected $_sAction = '';

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $_aErrors = array();

    /**
     * Success messages to display.
     *
     * @var array
     */
    protected $_aMessages = array();


    /**
     * Get a list of all shop modules.
     *
     * @return array
     */
    public function getModulesList()
    {
        /** @var oxModuleList $oModuleList */
        $oModuleList = oxNew( 'oxModuleList' );

        // Get all modules data
        $aAllModules = $oModuleList->getModulesFromDir( $this->getConfig()->getModulesDir() );

        // Exclude system modules like the Modules Config itself
        $aModules = array_diff_key( $aAllModules, array_combine( $this->_aExcludeModules, $this->_aExcludeModules ) );

        return $aModules;
    }

    /**
     * Get a list of settings available to export and import.
     *
     * @return array
     */
    public function getSettingsList()
    {
        $aSettings = array();

        foreach ( $this->_aModuleSettings as $sSetting ) {
            $aSettings[$sSetting] = sprintf(
                'OXPS_MODULESCONFIG_SETTING_%s',
                oxStr::getStr()->strtoupper( $sSetting )
            );
        }

        return $aSettings;
    }

    /**
     * Get current action name.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_sAction;
    }

    /**
     * Get detected errors list.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_aErrors;
    }

    /**
     * Get success messages list.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_aMessages;
    }

    /**
     * Export, backup amd import actions handler.
     * Collect form and import files data, validates it and performs the export or backup, or backup plus import.
     *
     * @return bool
     */
    public function actionSubmit()
    {
        $aRequestData = $this->_getRequestData();

        if ( !$this->_validateRequestData( $aRequestData ) ) {
            return false;
        }

        $sAction = $this->getAction();

        switch ( $sAction ) {
            case 'export':
                $this->_exportModulesConfig( $aRequestData );
                break;

            case 'backup':
                $this->_backupModuleSettings( $aRequestData );
                break;

            case 'import':
                $aImportData = $this->_getImportData();

                if ( !$this->_validateImportData( $aImportData ) ) {
                    return false;
                } else {
                    $this->_backupModuleSettings( $this->_getAllModulesAndSettingsData(), 'full_backup' );
                    $this->_importModulesConfig( $aRequestData, $aImportData );
                }

                break;

            default:
                break;
        }

        return false;
    }


    /*------------------------
     *- REQUEST DATA GETTERS -
     ------------------------*/

    /**
     * Collect action data from request parameters.
     * It includes affected modules list, settings list and action name.
     *
     * @return array
     */
    protected function _getRequestData()
    {
        $oConfig = $this->getConfig();

        $sAction   = '';
        $aModules  = (array) $oConfig->getRequestParameter( 'oxpsmodulesconfig_modules' );
        $aSettings = array_keys( (array) $oConfig->getRequestParameter( 'oxpsmodulesconfig_settings' ) );

        if ( $oConfig->getRequestParameter( 'oxpsmodulesconfig_export' ) ) {
            $sAction = 'export';
        } elseif ( $oConfig->getRequestParameter( 'oxpsmodulesconfig_backup' ) ) {
            $sAction = 'backup';
        } elseif ( $oConfig->getRequestParameter( 'oxpsmodulesconfig_import' ) ) {
            $sAction = 'import';
        }

        $this->_sAction = $sAction;

        return array('modules' => $aModules, 'settings' => $aSettings, 'action' => $sAction);
    }

    /**
     * Simulate action data with all modules elected and all settings checked.
     *
     * @return array
     */
    protected function _getAllModulesAndSettingsData()
    {
        return array(
            'modules'  => array_keys( $this->getModulesList() ),
            'settings' => array_keys( $this->getSettingsList() ),
            'action'   => ''
        );
    }

    /**
     * Get uploaded import files data.
     *
     * @return null|array
     */
    protected function _getImportData()
    {
        $oConfig = $this->getConfig();

        return $oConfig->getUploadedFile( 'oxpsmodulesconfig_file' );
    }


    /*--------------------
     *---- VALIDATION ----
     --------------------*/

    /**
     * Validate request data and collect errors if any.
     *
     * @param array $aData
     *
     * @return bool
     */
    protected function _validateRequestData( array $aData )
    {
        $this->_aErrors = array();

        $this->_validateModulesData( $aData );
        $this->_validateSettingsData( $aData );
        $this->_validateActionData( $aData );

        return empty( $this->_aErrors );
    }

    /**
     * Validate uploaded import file data and content and collect errors if any.
     *
     * @param array $aData
     *
     * @return bool
     */
    protected function _validateImportData( array $aData )
    {
        if ( empty( $aData ) ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_NO_FILE';
        }

        if ( !empty( $aData['error'] ) ) {
            $this->_setFileUploadError( $aData['error'] );
        }

        $this->_validateImportFile( $aData );
        $this->_validateJsonData( $aData );

        return empty( $this->_aErrors );
    }

    /**
     * Modules data should not be empty and each module ID should be available.
     *
     * @param array $aData
     */
    protected function _validateModulesData( array $aData )
    {
        if ( empty( $aData['modules'] ) or !is_array( $aData['modules'] ) ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_NO_MODULES';
        }

        $aValidModules = $this->getModulesList();

        foreach ( $aData['modules'] as $sModule ) {
            if ( !array_key_exists( $sModule, $aValidModules ) ) {
                $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_INVALID_MODULE';
                break;
            }
        }
    }

    /**
     * Settings data should not be empty and each setting name should be available.
     *
     * @param array $aData
     */
    protected function _validateSettingsData( array $aData )
    {
        if ( empty( $aData['settings'] ) or !is_array( $aData['settings'] ) ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_NO_SETTINGS';
        }

        $aValidSettings = $this->getSettingsList();

        foreach ( $aData['settings'] as $sSettings ) {
            if ( !array_key_exists( $sSettings, $aValidSettings ) ) {
                $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_INVALID_SETTING';
                break;
            }
        }
    }

    /**
     * Action name should be not empty and among available actions.
     *
     * @param array $aData
     */
    protected function _validateActionData( array $aData )
    {
        if ( empty( $aData['action'] ) or !in_array( $aData['action'], array('export', 'backup', 'import') ) ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_INVALID_ACTION';
        }
    }

    /**
     * Set error by file upload error code.
     *
     * @param int $iFileUploadErrorCode
     */
    protected function _setFileUploadError( $iFileUploadErrorCode )
    {
        $aFileUploadErrors = array(
            UPLOAD_ERR_INI_SIZE   => 'OXPS_MODULESCONFIG_ERR_FILE_SIZE',
            UPLOAD_ERR_FORM_SIZE  => 'OXPS_MODULESCONFIG_ERR_FILE_SIZE',
            UPLOAD_ERR_PARTIAL    => 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR',
            UPLOAD_ERR_NO_FILE    => 'OXPS_MODULESCONFIG_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR => 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR',
            UPLOAD_ERR_CANT_WRITE => 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR',
            UPLOAD_ERR_EXTENSION  => 'OXPS_MODULESCONFIG_ERR_FILE_TYPE',
        );

        $sErrorCode = array_key_exists( $iFileUploadErrorCode, $aFileUploadErrors ) ?
            $aFileUploadErrors[$iFileUploadErrorCode] :
            'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR';

        $this->_aErrors[] = $sErrorCode;
    }

    /**
     * Validate uploaded file to be of JSON type and readable.
     *
     * @param array $aFileData
     */
    protected function _validateImportFile( array $aFileData )
    {
        if ( empty( $aFileData['type'] ) or
             !in_array( $aFileData['type'], array('application/json', 'application/octet-stream') )
        ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_FILE_TYPE';
        }

        if ( !is_file( $aFileData['tmp_name'] ) or !is_readable( $aFileData['tmp_name'] ) ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_CANNOT_READ_FILE';
        }
    }

    /**
     * Set JSON import data from file and check modules configuration data for errors.
     *
     * @param array $aFileData
     */
    protected function _validateJsonData( array $aFileData )
    {
        /** @var oxpsModulesConfigTransfer $oModulesConfig */
        $oModulesConfig = oxNew( 'oxpsModulesConfigTransfer' );
        $oModulesConfig->setImportDataFromFile( $aFileData );

        $this->_aErrors = array_merge( $this->_aErrors, $oModulesConfig->getImportDataValidationErrors() );
    }


    /*--------------------
     *----- ACTIONS ------
     --------------------*/

    /**
     * Export checked settings of the selected modules to JSON file and pass it for download.
     *
     * @param array $aData
     */
    protected function _exportModulesConfig( array $aData )
    {
        /** @var oxpsModulesConfigTransfer $oModulesConfig */
        $oModulesConfig = oxNew( 'oxpsModulesConfigTransfer' );
        $oModulesConfig->exportForDownload( $aData );

        $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_EXPORT_FAILED';
    }

    /**
     * Export checked settings of the selected modules to JSON file and save it in file system.
     *
     * @param array  $aData
     * @param string $sBackupFileSuffix
     */
    protected function _backupModuleSettings( array $aData, $sBackupFileSuffix = '' )
    {
        /** @var oxpsModulesConfigTransfer $oModulesConfig */
        $oModulesConfig = oxNew( 'oxpsModulesConfigTransfer' );

        if ( !$oModulesConfig->backupToFile( $aData, $sBackupFileSuffix ) ) {
            $this->_aErrors[] = 'OXPS_MODULESCONFIG_ERR_BACKUP_FAILED';
        } else {
            $this->_aMessages[] = 'OXPS_MODULESCONFIG_MSG_BACKUP_SUCCESS';
        }
    }

    /**
     * Initialize import helper with uploaded file data and perform import for checked settings of selected modules.
     * On successful import clear eShop cache.
     *
     * @todo: Trigger modules events and update views?
     *
     * @param array $aRequestData
     * @param array $aImportData
     */
    protected function _importModulesConfig( array $aRequestData, array $aImportData )
    {
        /** @var oxpsModulesConfigTransfer $oModulesConfig */
        $oModulesConfig = oxNew( 'oxpsModulesConfigTransfer' );
        $oModulesConfig->setImportDataFromFile( $aImportData );

        if ( !$oModulesConfig->importData( $aRequestData ) ) {
            $this->_aErrors = array_merge( $this->_aErrors, $oModulesConfig->getImportErrors() );
        } else {
            $this->_aMessages[] = 'OXPS_MODULESCONFIG_MSG_IMPORT_SUCCESS';

            /** @var oxpsModulesConfigModule $oModule */
            $oModule = oxRegistry::get( 'oxpsModulesConfigModule' );
            $oModule::cleanTmp();
        }
    }
}

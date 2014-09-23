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
 * Class oxpsModulesConfigTransfer
 * Modules configuration export, backup and import actions handler.
 *
 * @todo ddr: Refactor the class to fit 500 lines: Setting (load / save)
 */
class oxpsModulesConfigTransfer extends oxSuperCfg
{

    /**
     * Import data.
     *
     * @var array
     */
    protected $_aImportData = array();


    /**
     * Collect requested settings for selected modules, build JSON export file and pass it for download.
     *
     * @param array $aExportParameters
     */
    public function exportForDownload( array $aExportParameters )
    {
        $aExportData = $this->_getSettingsData( $aExportParameters );
        $sFileName   = $this->_getJsonFileName();

        header( 'Content-disposition: attachment; filename=' . $sFileName );
        header( 'Content-type: application/json' );

        exit( json_encode( $aExportData ) );
    }

    /**
     * Collect requested settings for selected modules, build JSON backup file and save it in file system.
     *
     * @param array  $aBackupParameters
     * @param string $sBackupFileSuffix
     *
     * @return int
     */
    public function backupToFile( array $aBackupParameters, $sBackupFileSuffix = 'manual_backup' )
    {
        $aBackupData  = $this->_getSettingsData( $aBackupParameters );
        $sBackupsPath = $this->_getBackupFolderPath();
        $sFileName    = $this->_getJsonFileName( $sBackupFileSuffix );

        return file_put_contents( $sBackupsPath . $sFileName, json_encode( $aBackupData ) );
    }

    /**
     * Get import data from uploaded file and set it decoded from JSON to array.
     *
     * @param array $aImportFileData
     */
    public function setImportDataFromFile( array $aImportFileData )
    {
        if ( !empty( $aImportFileData['tmp_name'] ) and is_file( $aImportFileData['tmp_name'] ) ) {
            $sData = file_get_contents( $aImportFileData['tmp_name'] );

            if ( !empty( $sData ) ) {
                $this->_aImportData = (array) json_decode( $sData );
            }
        }
    }

    /**
     * Validate import data and return errors list if any.
     *
     * @return array
     */
    public function getImportDataValidationErrors()
    {
        /** @var oxpsModulesConfigValidator $oImportDataValidator */
        $oImportDataValidator = oxRegistry::get( 'oxpsModulesConfigValidator' );
        $oImportDataValidator->init( $this->_aImportData, $this->_getSettingsDataHeader() );

        return (array) $oImportDataValidator->validate();
    }

    /**
     * Import modules configuration data for checked settings of selected modules.
     *
     * @todo: Logging to file.
     * @todo: Roll back to last automatic full backup on failure.
     *
     * @param array $aParameters
     *
     * @return bool
     */
    public function importData( array $aParameters )
    {
        $aImportData    = (array) reset( $this->_aImportData );
        $aImportModules = (array) $aImportData['aModules'];

        foreach ( $aParameters['modules'] as $sModuleId ) {
            if ( array_key_exists( $sModuleId, $aImportModules ) ) {
                $aImportModule = (array) $aImportModules[$sModuleId];

                foreach ( $aParameters['settings'] as $sSetting ) {
                    $this->_setSettingValue( $sModuleId, $sSetting, $aImportModule[$sSetting] );
                }
            }
        }

        return true;
    }

    /**
     * Get errors that occurred during import.
     *
     * @todo: Implement it when Logging is implemented (should have both success log and import errors)
     *
     * @return array
     */
    public function getImportErrors()
    {
        return array();
    }

    /**
     * Collect requested settings for selected modules.
     *
     * @param array $aParameters
     *
     * @return array
     */
    protected function _getSettingsData( array $aParameters )
    {
        $aModules = array();

        foreach ( $aParameters['modules'] as $sModuleId ) {

            if ( !array_key_exists( $sModuleId, $aModules ) ) {
                $aModules[$sModuleId] = array();
            }

            foreach ( $aParameters['settings'] as $sSetting ) {
                $aModules[$sModuleId][$sSetting] = $this->_getSettingValue( $sModuleId, $sSetting );
            }
        }

        return $this->_getSettingsDataHeader( $aModules );
    }

    /**
     * Create a name for JSON export or backup file.
     *
     * @param string $sBackupSuffix Additional prefix for backup file name
     *
     * @return string
     */
    protected function _getJsonFileName( $sBackupSuffix = '' )
    {
        return sprintf(
            'oxid_modules_config_%s%s.json',
            date( 'Y-m-d_H-i-s' ),
            empty( $sBackupSuffix ) ? '' : ( '.' . $sBackupSuffix )
        );
    }

    /**
     * Get full path to eShop export dir to store modules configuration backups in.
     * If modules configuration backups folder is missing,
     * it creates the folder and .htaccess file to forbid direct web access.
     *
     * @return string
     */
    protected function _getBackupFolderPath()
    {
        /** @var oxConfig $oConfig */
        $oConfig = $this->getConfig();

        $sShopDirPath   = (string) $oConfig->getConfigParam( 'sShopDir' );
        $sBackupDirPath = $sShopDirPath . 'export' . DIRECTORY_SEPARATOR . 'modules_config' . DIRECTORY_SEPARATOR;

        if ( !is_dir( $sBackupDirPath ) ) {
            mkdir( $sBackupDirPath, 0777 );
            file_put_contents( $sBackupDirPath . '.htaccess', 'deny from all' . PHP_EOL );
        }

        return $sBackupDirPath;
    }

    /**
     * Get an array with basic shop data for modules data to set in.
     * Put modules data array argument inside it.
     *
     * @param array $aModules
     *
     * @return array
     */
    protected function _getSettingsDataHeader( array $aModules = array() )
    {
        /** @var oxConfig $oConfig */
        $oConfig = $this->getConfig();

        return array(
            '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                'sShopVersion' => $oConfig->getVersion(),
                'sShopEdition' => $oConfig->getEdition(),
                'sShopId'      => $oConfig->getShopId(),
                'aModules'     => $aModules,
            )
        );
    }

    /**
     * Get setting values by setting name.
     * Maps setting name to its related class and key or field.
     *
     * @param string $sModuleId
     * @param string $sSetting
     *
     * @return mixed
     */
    protected function _getSettingValue( $sModuleId, $sSetting )
    {
        /** @var oxpsModulesConfigStorage $oConfigurationStorage */
        $oConfigurationStorage = oxRegistry::get( 'oxpsModulesConfigStorage' );

        return $oConfigurationStorage->load( $sModuleId, $sSetting );
    }

    /**
     * Set module setting value.
     * Maps setting name to its related class and key or field and saves the value.
     *
     * @param string $sModuleId
     * @param string $sSetting
     * @param mixed  $mValue
     */
    protected function _setSettingValue( $sModuleId, $sSetting, $mValue )
    {
        /** @var oxpsModulesConfigStorage $oConfigurationStorage */
        $oConfigurationStorage = oxRegistry::get( 'oxpsModulesConfigStorage' );

        $oConfigurationStorage->save( $sModuleId, $sSetting, $mValue );
    }
}

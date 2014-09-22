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
 */
class oxpsModulesConfigTransfer extends oxSuperCfg
{

    /**
     * Settings map.
     * Maps setting name from metadata to its related class name or title and key.
     *
     * @var array
     */
    protected $_settingsMap = array(
        'version'   => array('oxConfig', 'aModuleVersions'),
        'extend'    => array('oxConfig-Common', 'aModules'),
        'files'     => array('oxConfig', 'aModuleFiles'),
        'templates' => array('oxConfig', 'aModuleTemplates'),
        'blocks'    => array('', ''),
        'settings'  => array('oxConfig-List', ''),
        'events'    => array('oxConfig', 'aModuleEvents'),
    );


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

    public function setImportDataFromFile( array $aImportFileData )
    {
        //TODO DDR
    }

    public function getImportDataValidationErrors()
    {
        return array(); //todo ddr
    }

    public function importData( array $aImportParameters )
    {
        return true; //todo ddr
    }

    public function getImportErrors()
    {
        return array(); //todo ddr
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
    protected function _getSettingsDataHeader( array $aModules )
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
        list( $sSettingClass, $sSettingField ) = $this->_getSettingMap( $sSetting );

        return $this->_loadSettingValue( $sModuleId, $sSettingClass, $sSettingField );
    }

    /**
     * Get setting object and key by setting name.
     *
     * @param string $sSetting
     *
     * @return array
     */
    protected function _getSettingMap( $sSetting )
    {
        if ( !array_key_exists( $sSetting, $this->_settingsMap ) ) {
            return array('', '');
        }

        return $this->_settingsMap[$sSetting];
    }

    /**
     * Load setting by module ID, setting class name/title and related setting key.
     * It could be either module related value from oxConfig or common modules setting,
     * module settings list or module blocks list.
     *
     * @param string $sModuleId
     * @param string $sClassTitle
     * @param string $sKey
     *
     * @return array|mixed|null
     */
    protected function _loadSettingValue( $sModuleId, $sClassTitle, $sKey )
    {
        $mSetting = null;

        if ( empty( $sClassTitle ) or empty( $sKey ) ) {
            return $mSetting;
        }

        if ( $sClassTitle === 'oxConfig' ) {
            $mAllSetting = $this->getConfig()->getShopConfVar( $sKey );
            $mSetting    = $this->_filterByModule( $mAllSetting, $sModuleId );
            // TODO DDR other types
        }

        return $mSetting;
    }

    /**
     * Get settings only for a module.
     *
     * @param array|mixed $mAllSetting
     * @param string      $sModuleId
     *
     * @return array|mixed
     */
    protected function _filterByModule( $mAllSetting, $sModuleId )
    {
        if ( is_array( $mAllSetting ) and array_key_exists( $sModuleId, $mAllSetting ) ) {
            return $mAllSetting[$sModuleId];
        }

        return null;
    }
}

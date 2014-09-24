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
 * Class oxpsModulesConfigModule
 * Extends oxModule class handles module setup, provides additional tools.
 */
class oxpsModulesConfigModule extends oxModule
{

    /**
     * The module instance.
     *
     * @var oxpsModulesConfigModule
     */
    private static $_instance = null;


    /**
     * Class constructor.
     * Sets main module data and load additional data.
     */
    function __construct( $sModuleTitle = 'OXPS Modules Config',
                          $sModuleDescription = 'Modules configuration export and import tools' )
    {
        $sModuleId = 'oxpsmodulesconfig';

        $this->setModuleData(
            array(
                'id'          => $sModuleId,
                'title'       => $sModuleTitle,
                'description' => $sModuleDescription,
            )
        );

        $this->load( $sModuleId );

        oxRegistry::set( 'oxpsModulesConfigModule', $this );
    }


    /**
     * Returns the module instance
     *
     * @return oxpsModulesConfigModule
     */
    public static function getInstance()
    {
        return oxRegistry::get( 'oxpsModulesConfigModule' );
    }

    /**
     * Module activation script.
     */
    public static function onActivate()
    {
        self::cleanTmp();
    }

    /**
     * Module deactivation script.
     */
    public static function onDeactivate()
    {
        self::cleanTmp();
    }

    /**
     * Delete cache files.
     *
     * @return bool
     */
    public static function cleanTmp( $sClearFolderPath = '' )
    {
        $sTempFolderPath = oxRegistry::getConfig()->getConfigParam( 'sCompileDir' );

        if ( !empty( $sClearFolderPath ) and
             ( strpos( $sClearFolderPath, $sTempFolderPath ) !== false ) and
             is_dir( $sClearFolderPath )
        ) {

            // User argument folder path to delete from
            $sFolderPath = $sClearFolderPath;
        } elseif ( empty( $sClearFolderPath ) ) {

            // Use temp folder path from settings
            $sFolderPath = $sTempFolderPath;
        } else {
            return false;
        }

        $hDir = opendir( $sFolderPath );

        if ( !empty( $hDir ) ) {
            while ( false !== ( $sFileName = readdir( $hDir ) ) ) {
                $sFilePath = $sFolderPath . '/' . $sFileName;

                if ( !in_array( $sFileName, array('.', '..', '.gitkeep', '.htaccess') ) and is_file( $sFilePath ) ) {

                    // Delete a file if it is allowed to delete
                    @unlink( $sFilePath );
                } elseif ( $sFileName == 'smarty' and is_dir( $sFilePath ) ) {

                    // Recursive call to clean Smarty temp
                    self::cleanTmp( $sFilePath );
                }
            }
        }

        return true;
    }

    /**
     * Get translated string bt the translation code.
     *
     * @param string  $sCode
     * @param boolean $blUseModulePrefix User module translations prefix or not.
     *
     * @return string
     */
    public function translate( $sCode, $blUseModulePrefix = true )
    {
        if ( $blUseModulePrefix ) {
            $sCode = 'OXPS_MODULESCONFIG_' . $sCode;
        }

        return oxRegistry::getLang()->translateString( $sCode, oxRegistry::getLang()->getBaseLanguage(), false );
    }


    /**
     * Get module setting value.
     *
     * @param string  $sModuleSettingName Module setting parameter name without module prefix.
     * @param boolean $blUseModulePrefix  User module settings prefix or not.
     *
     * @return mixed
     */
    public function getSetting( $sModuleSettingName, $blUseModulePrefix = true )
    {
        if ( $blUseModulePrefix ) {
            $sModuleSettingName = 'oxpsModulesConfig' . (string) $sModuleSettingName;
        }

        return oxRegistry::getConfig()->getConfigParam( (string) $sModuleSettingName );
    }

    /**
     * Get module path.
     *
     * @return string Full path to module dir.
     */
    public function getPath()
    {
        return oxRegistry::getConfig()->getModulesDir() . 'oxps/modulesconfig/';
    }
}

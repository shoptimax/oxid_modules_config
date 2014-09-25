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
}

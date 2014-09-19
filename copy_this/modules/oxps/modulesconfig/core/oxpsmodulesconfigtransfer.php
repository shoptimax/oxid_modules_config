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
     * Collect requested settings for selected modules, build JSON export file
     *
     * @param array $aExportParameters
     */
    public function exportForDownload( array $aExportParameters )
    {
        //D::d($aExportParameters); //TODO ddr: class not found error persists.

        /** @var oxConfig $oConfig */
        $oConfig = $this->getConfig();

        // TODO DDR: Sandbox demo
        $aExportData = array(
            'OXID_ESHOP_MODULES_CONFIGURATION' => array(
                'sShopVersion'          => '5.1.6',
                'sShopEdition'          => 'EE',
                'sShopId'               => 1,
                'aModulesConfiguration' => array(),
            )
        );

        foreach ( $aExportParameters['modules'] as $sModuleId ) {

            if ( !array_key_exists( $sModuleId, $aExportData['aModulesConfiguration'] ) ) {
                $aExportData['aModulesConfiguration'][$sModuleId] = array();
            }

            //todo ddr foreach with map through settings
            //extend => config => getShopConfVar => aModules
            //...todo ddr map all rest
            $aExportData['aModulesConfiguration'][$sModuleId]['extend'] = $oConfig->getShopConfVar(
                'aModules',
                null,
                'modules:' . $sModuleId
            );
        }

        print json_encode( $aExportData );
    }
}

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
 * Class oxpsModulesConfigContent
 * Module configuration import and export content handler defines what data is used in the processes.
 */
class oxpsModulesConfigContent extends oxSuperCfg
{

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
     * Get a list of all shop modules.
     *
     * @return array
     */
    public function getModulesList()
    {
        /** @var oxModuleList $oModuleList */
        $oModuleList = oxNew('oxModuleList');

        // Get all modules data
        $aAllModules = $oModuleList->getModulesFromDir($this->getConfig()->getModulesDir());

        // Exclude system modules like the OXID Module Configuration Im-/Exporter itself
        $aModules = array_diff_key($aAllModules, array_combine($this->_aExcludeModules, $this->_aExcludeModules));

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

        foreach ($this->_aModuleSettings as $sSetting) {
            $aSettings[$sSetting] = sprintf('OXPS_MODULESCONFIG_SETTING_%s', oxStr::getStr()->strtoupper($sSetting));
        }

        return $aSettings;
    }
}

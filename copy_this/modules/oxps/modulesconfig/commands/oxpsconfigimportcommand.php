<?php
/**
 * This file is part of OXID Console.
 *
 * OXID Console is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID Console is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID Console.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */

require_once "oxpsconfigcommandbase.php";


/**
 * Class OxpsConfigImportCommandBase
 */
class OxpsConfigImportCommand extends OxpsConfigCommandBase
{

    /**
     * @var oxConfig $oConfig
     */
    protected $oConfig;

    /**
     * @var int The shop id to load the config for.
     */
    protected $sShopId;

    /**
     * @var oxModuleStateFixer
     */
    protected $oModuleStateFixer;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('config:import');
        $this->setDescription('Import shop config');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: config:import [options]');
        $oOutput->writeLn();
        $oOutput->writeLn('This command imports shop config');
        $oOutput->writeLn();
        $oOutput->writeLn('Available options:');
        $oOutput->writeLn('  -n, --no-debug     No debug output');
        $oOutput->writeLn('  -e, --env          Environment');
        $oOutput->writeLn('  -s, --shop         Shop');
    }

    /**
     * Execute current command
     *
     * @param oxIOutput $oOutput
     */
    public function execute(oxIOutput $oOutput)
    {
        $this->init($oOutput);
        // import environment specific config values

        $aMetaConfig = $this->readConfigValues($this->getShopsConfigFileName());
        $aShops      = $aMetaConfig['shops'];
        foreach ($aShops as $sShop => $sFileName) {
            $this->sShopId = $sShop;
            $this->runShopConfigImport($sShop, $sFileName);
        }

        $this->getDebugOutput()->writeLn("done");
    }

    /**
     * runShopConfigImport
     *
     * @param $sShop
     * @param $sRelativeFileName
     *
     * @throws Exception
     */
    protected function runShopConfigImport($sShop, $sRelativeFileName)
    {

        $sFileName = $this->getConfigDir() . $sRelativeFileName;
        $aResult   = $this->readConfigValues($sFileName);

        $aResult = $this->merge_config($this->aDefaultConfig, $aResult);

        if ($this->sEnv) {
            $sEnvDirName = $this->getEnviromentConfigDir();
            $sFileName   = $sEnvDirName . $sRelativeFileName;
            $aEnvConfig  = $this->readConfigValues($sFileName);
            $aResult     = $this->merge_config($aResult, $aEnvConfig);
        }

        $this->oOutput->writeLn("Importing config for shop $sShop");

        $this->importConfigValues($aResult, true);
    }

    /**
     * merge tow config arrays
     *
     * @param $aBase
     * @param $aOverride
     *
     * @return
     */
    protected function merge_config($aBase, $aOverride)
    {
        foreach ($aOverride as $key => $mOverriderValue) {
            if (is_array($mOverriderValue)) {
                $aBaseValue = $aBase[$key];
                if ($aBaseValue) {
                    if (is_array($aBaseValue)) {
                        if ($key == 'module') {
                            foreach ($mOverriderValue as $sModuleId => $aModuleInfo) {
                                if ($aBaseValue[$sModuleId]) {
                                    $aBaseValue[$sModuleId] = array_merge($aBaseValue[$sModuleId], $aModuleInfo);
                                } else {
                                    $aBaseValue[$sModuleId] = $aModuleInfo;
                                }
                            }
                            $mOverriderValue = $aBaseValue;
                        } else {
                            $mOverriderValue = array_merge($aBaseValue, $mOverriderValue);
                        }
                    } else {
                        $this->oOutput->writeLn("Corrupted config value $key for shop " . $this->sShopId);
                    }
                }
            }
            $aBase[$key] = $mOverriderValue;
        }

        return $aBase;
    }

    /**
     * @param $aConfigValues
     */
    protected function importShopsConfig($aConfigValues)
    {
        /**
         * @var oxshop $oShop
         */
        $oShop   = oxNew("oxshop");
        $sShopId = $this->sShopId;
        if (!$oShop->load($sShopId)) {
            $this->oOutput->writeLn("[WARN] Creating new shop $sShopId");
            $oShop->setId($sShopId);
            $oConfig = oxSpecificShopConfig::get(1);
            $oConfig->saveShopConfVar(
                'arr',
                'aModules',
                [],
                $sShopId,
                ""
            );
        }
        $aOxShopSettings = $aConfigValues['oxshops'];
        if ($aOxShopSettings) {
            $oShop->assign($aOxShopSettings);
            $oShop->save();
            for ($i = 1; $i <= 3; $i++) {
                $oShop->setLanguage($i);
                foreach ($aOxShopSettings as $sVarName => $mVarValue) {
                    $iPosLastChar   = strlen($sVarName) - 1;
                    $iPosUnderscore = $iPosLastChar - 1;
                    if ($sVarName[$iPosUnderscore] == '_' && $sVarName[$iPosLastChar] == $i) {
                        $sFiledName                   = substr($sVarName, 0, strlen($sVarName) - 2);
                        $aOxShopSettings[$sFiledName] = $mVarValue;
                    }
                }
                $oShop->assign($aOxShopSettings);
                $oShop->save();
            }
        }
    }

    /*
     * @param string $sShopId
     * @param array $aConfigValues
     * @param bool $blRestoreModuleDefaults
     */
    protected function importConfigValues($aConfigValues, $blRestoreModuleDefaults = false)
    {
        $sShopId = $this->sShopId;
        $this->importShopsConfig($aConfigValues, $blRestoreModuleDefaults);

        $oConfig       = oxSpecificShopConfig::get($sShopId);
        $this->oConfig = $oConfig;

        /** @var oxModuleStateFixer $oModuleStateFixer */
        $oModuleStateFixer = oxRegistry::get('oxModuleStateFixer');
        $oModuleStateFixer->setConfig($oConfig);
        $this->oModuleStateFixer = $oModuleStateFixer;

        $oxModuleList = oxNew('oxModuleList');
        //only do the following on the first run when $blRestoreModuleDefaults = true
        if ($blRestoreModuleDefaults) {
            //this will scan the module directory and add all modules (module paths),
            //that is something fixstates does not do.
            //this must be done before aDisabledModules is restored because this function deaktivate modules
            /**
             * @var oxModuleList $oxModuleList
             */
            $oxModuleList->getModulesFromDir(oxRegistry::getConfig()->getModulesDir());

            $aModules = $oxModuleList->getList();
            foreach ($aModules as $sModuleId => $oModule) {
                // restore default module settings
                /** @var oxModule $oModule */
                $aDefaultModuleSettings = $oModule->getInfo("settings");
                if ($aDefaultModuleSettings) {
                    foreach ($aDefaultModuleSettings as $aValue) {
                        $sVarName  = $aValue["name"];
                        $mVarValue = $aValue["value"];

                        $oConfig->saveShopConfVar(
                            $aValue["type"],
                            $sVarName,
                            $mVarValue,
                            $sShopId,
                            "module:$sModuleId"
                        );
                    }
                }
            }
        }

        $aModuleVersions = [];

        $aGeneralSettings = $aConfigValues[$this->sNameForGeneralShopSettings];
        $sSectionModule   = '';
        foreach ($aGeneralSettings as $sVarName => $mVarValue) {
            if ($sVarName == 'aModules') {
                $aModulesTmp = [];
                foreach ($mVarValue as $sBaseClass => $aClassNames) {
                    $sAmpSeparatedClassNames  = join('&', $aClassNames);
                    $aModulesTmp[$sBaseClass] = $sAmpSeparatedClassNames;
                }
                $mVarValue = $aModulesTmp;
            } elseif ($sVarName == 'aModuleVersions') {
                $aModuleVersions = $mVarValue;
            }
            $this->saveShopVar($sVarName, $mVarValue, $sSectionModule);
        }

        $this->importModuleConfig($aConfigValues['module']);

        $this->importThemeConfig($aConfigValues['theme'], $sShopId);

        /** @var oxModule $oModule */
        $oModule = oxNew('oxModule');
        foreach ($aModuleVersions as $sModuleId => $sVersion) {
            if (!$oModule->load($sModuleId)) {
                $this->oOutput->writeLn("[ERROR] {$sModuleId} does not exist - skipping");
                continue;
            }

            //fix state again because class chain was reseted by the import above
            $oModuleStateFixer->fix($oModule);

            //execute activate event
            if ($this->aConfiguration['executeModuleActivationEvents'] && $oModule->isActive()) {
                $oModuleStateFixer->activate($oModule);
            }
            $sCurrentVersion = $oModule->getInfo("version");
            if ($sCurrentVersion != $sVersion) {
                $this->oOutput->writeLn(
                    "[ERROR] {$sModuleId} version on export" .
                    " $sVersion vs current version $sCurrentVersion"
                );
                $aModuleVersions[$sModuleId] = $sCurrentVersion;
                $this->saveShopVar('aModuleVersions', $aModuleVersions, '');
            }
        }
    }

    protected function getTypeAndValue($sVarName, $mVarValue)
    {
        if ($this->is_assoc_array($mVarValue)) {
            $iCount = count($mVarValue);
            if ($iCount == 0) {
                $sVarType = 'arr';
            } elseif ($iCount > 1) {
                $sVarType = 'aarr';
            } else {
                $sVarType  = key($mVarValue);
                $mVarValue = $mVarValue[$sVarType];
            }
        } elseif (is_array($mVarValue)) {
            $sVarType = 'arr';
        } else {
            if (substr($sVarName, 0, 2) === "bl") {
                $sVarType = 'bool';
            } else {
                $sVarType = 'str';
            }
        }

        return array($sVarType, $mVarValue);
    }

    protected function saveShopVar($sVarName, $mVarValue, $sSectionModule)
    {
        $sShopId = $this->sShopId;
        $oConfig = $this->oConfig;

        list($sVarType, $mVarValue) = $this->getTypeAndValue($sVarName, $mVarValue);

        $oConfig->saveShopConfVar(
            $sVarType,
            $sVarName,
            $mVarValue,
            $sShopId,
            $sSectionModule
        );
    }

    protected function is_assoc_array($arr)
    {
        return is_array($arr) && (array_keys($arr) !== range(0, count($arr) - 1));
    }

    /**
     * @param array    $aSectionData
     * @param oxModule $oModule
     *
     * @return array
     */
    protected function importModuleConfig($aModules)
    {
        /** @var oxModule $oModule */
        $oModule = oxNew('oxModule');
        if ($aModules == null) {
            return;
        }
        foreach ($aModules as $sModuleId => $aModuleSettings) {
            if (!$oModule->load($sModuleId)) {
                $this->oOutput->writeLn("[ERROR] {$sModuleId} does not exist - skipping");

                continue;
            }
            // set module config values that are explicitly included in this import
            foreach ($aModuleSettings as $sVarName => $mVarValue) {
                $this->saveShopVar($sVarName, $mVarValue, "module:$sModuleId");
            }
        }
    }

    /**
     * @param $aSectionData
     * @param $sShopId
     * @param $oConfig
     */
    protected function importThemeConfig($aThemes)
    {
        if ($aThemes == null) {
            return;
        }
        foreach ($aThemes as $sThemeId => $aSettings) {
            $sSectionModule = "theme:$sThemeId";
            foreach ($aSettings as $sVarName => $mVarValue) {
                $this->saveShopVar($sVarName, $mVarValue, $sSectionModule);
            }
        }
    }
}

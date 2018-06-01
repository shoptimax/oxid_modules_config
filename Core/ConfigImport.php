<?php
/**
 * This file is part of OXID Module Configuration Im-/Exporter module.
 *
 * OXID Module Configuration Im-/Exporter module is free software:
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * OXID Module Configuration Im-/Exporter module is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID Module Configuration Im-/Exporter module.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @category      module
 * @package       modulesconfig
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */
 
namespace Oxps\ModulesConfig\Core;

use oxconfig;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Exception\FileException;
use OxidEsales\Eshop\Core\UtilsObject;
use oxmodulelist;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class ConfigImport
 * Implements functionality for the oxpsConfigImportCommand
 */
class ConfigImport extends CommandBase
{

    /**
     * @var Config $oConfig
     */
    protected $oConfig;

    /**
     * @var int The shop id to load the config for.
     */
    protected $sShopId;

    /**
     * @var array "name+module" => type
     * used to check if the imported config value type matches the stored type in the oxconfig table
     * if not the type must be overridden.
     * it helps to avoid unnecessary db rights on deployment
     */
    protected $storedVarTypes = [];

    public function configure()
    {
        $this->setName('config:import-internal')
            ->setDescription('Import shop config')
            ->addOption(
                'no-debug',
                null,//can not use n
                InputOption::VALUE_NONE,
                'No debug ouput',
                null
            )
            ->addOption(
                'env',
                null,
                InputOption::VALUE_OPTIONAL,
                'Environment',
                null
            )
            ;
    }
    
    /**
     * Executes all functionality which is necessary for a call of OXID console config:import
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws StandardException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // import environment specific config values
            $aMetaConfig = $this->readConfigValues($this->getShopsConfigFileName());
            $aShops = $aMetaConfig['shops'];

            $this->runShopConfigImportForAllShops($aShops);
            printf("Import successfully finished!\n");
        } catch (ParseException $e) {
            printf("ParseException: Could not parse a YAML File: ".$e->getMessage()."\n");
            exit(1);
        } catch (FileException $oEx) {
            printf("FileException: Could not complete: ".$oEx->getMessage()."\n");
            exit(2);
        } catch (RuntimeException $e) {
            printf("RuntimeException: Could not complete: ".$e->getMessage()."\n");
            exit(3);
        }
    }
    
    /**
     * runShopConfigImportForOneShop
     *
     * @param $sShop
     * @param $sRelativeFileName
     *
     * @throws StandardException
     * @throws \oxfileexception
     */
    protected function runShopConfigImportForOneShop($sShop, $sRelativeFileName)
    {

        $sFileName = $this->getConfigDir() . $sRelativeFileName;
        $aResult = $this->readConfigValues($sFileName);
        $aResult = $this->merge_config($this->aDefaultConfig, $aResult);
        if ($this->sEnv) {
            $sEnvDirName = $this->getEnvironmentConfigDir();
            $sFileName = $sEnvDirName . $sRelativeFileName;
            $aEnvConfig = $this->readConfigValues($sFileName);
            $aResult = $this->merge_config($aResult, $aEnvConfig);
        }
        
        printf("Importing config for shop ".$sShop."\n");
        $this->importConfigValues($aResult);
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
                        printf("ERROR: Ignoring corrupted common config value '$key':'$aBaseValue' for shop " . $this->sShopId."\n");
                    }
                }
            } else {
                printf("ERROR: Skipping corrupted config value '$key':'$mOverriderValue' for shop " . $this->sShopId."\n");
                continue;
            }
            $aBase[$key] = $mOverriderValue;
        }

        return $aBase;
    }
    
    /**
     * @param $aConfigValues
     *
     * @throws \Exception
     */
    protected function importShopsConfig($aConfigValues)
    {
        /**
         * @var Shop $oShop
         */
        $oShop = oxNew("oxshop");
        $sShopId = $this->sShopId;
        if (!$oShop->load($sShopId)) {
            printf("[WARNING] Creating new shop $sShopId"."\n");
            $oShop->setId($sShopId);
            $oConfig = SpecificShopConfig::get(1);
            $oConfig->saveShopConfVar(
                'arr',
                'aModules',
                array(),
                $sShopId,
                ""
            );
        }
        $oShop->setShopId($sShopId);
        $aOxShopSettings = $aConfigValues['oxshops'];
        if ($aOxShopSettings) {
            $oShop->assign($aOxShopSettings);
            //fake active shopid to allow derived update
            $oShop->getConfig()->setShopId($sShopId);
            $oShop->save();
            for ($i = 1; $i <= 3; $i++) {
                $oShop->setLanguage($i);
                foreach ($aOxShopSettings as $sVarName => $mVarValue) {
                    $iPosLastChar = strlen($sVarName) - 1;
                    $iPosUnderscore = $iPosLastChar - 1;
                    if ($sVarName[$iPosUnderscore] == '_' && $sVarName[$iPosLastChar] == $i) {
                        $sFiledName = substr($sVarName, 0, strlen($sVarName) - 2);
                        $aOxShopSettings[$sFiledName] = $mVarValue;
                    }
                }
                $oShop->assign($aOxShopSettings);
                $oShop->save();
            }
        }
    }
    
    /**
     * @param $aConfigValues
     *
     * @throws \Exception
     * @throws StandardException
     *
     * TODO: REFACTOR THIS METHOD, ADD MORE COMMENTS. (NOT READABLE)
     */
    protected function importConfigValues($aConfigValues)
    {
        $sShopId = $this->sShopId;
        $this->importShopsConfig($aConfigValues);

        $oConfig = SpecificShopConfig::get($sShopId);
        $this->oConfig = $oConfig;
        Registry::set(oxconfig::class,$oConfig);


        $disabledModulesBeforeImport = array_flip($oConfig->getConfigParam('aDisabledModules'));
        $disabledModulesBeforeImport = array_flip($disabledModulesBeforeImport);
        $modulesKnownBeforeImport = $oConfig->getConfigParam('aModuleVersions');

        $aModuleVersions = $this->getConfigValue($aConfigValues,'aModuleVersions');
        
        /** @var Module $oModule */
        $oModuleFixer = new ModuleStateFixer();
        /** @var Config $oConfig */
        $oModuleFixer->setConfig($oConfig);

        $oModule = new Module();
        /** @var Config $oConfig */
        $oModule->setConfig($oConfig);

        $updatedModules = [];
        $notLoadedModules = [];
        foreach ($aModuleVersions as $sModuleId => $sVersion) {
            $oldVersion = $modulesKnownBeforeImport[$sModuleId];
            $newVersion = $aModuleVersions[$sModuleId];
            if ($oldVersion != $newVersion) {
                $updatedModules[$sModuleId] = $sModuleId;
                if (isset($oldVersion)) {
                    printf("[INFO] {$sModuleId} has a different version ($oldVersion vs $newVersion) disabling it, so it can do updates"."\n");
                } else {
                    printf("[NOTE] {$sModuleId} $newVersion is new");
                }
                if (!$oModule->load($sModuleId)) {
                    //maybe fine for new modules because shop can not load them before
                    //modules directory was scanned (by "loadModulesFromDir"-method)
                    //later we will check if there is anything left that can still not be loaded
                    $notLoadedModules[] = $sModuleId;
                    continue;
                }
                if ($oModule != null) {
                    $disabledModulesBeforeImport[$sModuleId] = 'disabledByUpdate';
                    $oModule->deactivate($oModule);
                }
            }
        }

        $this->importModuleConfig($aConfigValues);

        $modulesKnownByPath = $oConfig->getConfigParam('aModulePaths');

        foreach ($notLoadedModules as $sModuleId) {
            if (!$oModule->load($sModuleId)) {
                printf("[WARN] can not load {$sModuleId} given in yaml, please make a fresh export without that module"."\n");
            }
        }

        $this->restoreGeneralShopSettings($aConfigValues);

        $this->importThemeConfig($aConfigValues['theme']);

        $aDisabledModules = $oConfig->getConfigParam('aDisabledModules');

        $aModulePathsClean = $modulesKnownByPath;
        foreach ($modulesKnownByPath as $sModuleId => $path) {
            if (!isset($aModuleVersions[$sModuleId])) {
                $isDisabled = array_search($sModuleId,$aDisabledModules);
                if (!$oModule->load($sModuleId)) {
                    unset ($aModulePathsClean[$sModuleId]);
                    printf("[WARN] {$sModuleId} it is not part of the import but, not installed physically, but somehow registered; removing it from modulePath array."."\n");
                    $oConfig->saveShopConfVar('aarr','aModulePaths',$aModulePathsClean);
                }
                if (!$isDisabled) {
                    printf("[WARN] disabling {$sModuleId} because it is not part of the import but installed on this system, please create a new export"."\n");
                    $aDisabledModules[] = $sModuleId;
                }
            }
        }
        $oConfig->saveShopConfVar('arr','aDisabledModules',$aDisabledModules);

        foreach ($aModuleVersions as $sModuleId => $sVersion) {
            if (!$oModule->load($sModuleId)) {
                printf("[ERROR] can not load {$sModuleId} given in importfile shop{$sShopId}.yaml in aModuleVersions please check if it is installed and working"."\n");
                continue;
            }

            //execute activate event
            if ($this->aConfiguration['executeModuleActivationEvents'] && $oModule->isActive()) {
                $wasDeactivatedBeforeImport = isset($modulesKnownBeforeImport[$sModuleId]) && isset($disabledModulesBeforeImport[$sModuleId]);
                $wasUnknownBeforeImport = !isset($modulesKnownBeforeImport[$sModuleId]);
                if ($wasDeactivatedBeforeImport || $wasUnknownBeforeImport) {
                    printf("[INFO] activating module ".$sModuleId."\n");
                    if ($oModule != null) {
                        $oModule->activate($oModule);
                    } else {
                        $oModule->activate();
                    }
                }
            }

            //fix state again because class chain was reset by the import above
            //also onActivate call event can cause duplicate tpl blocks
            if ($oModule != null) {
                if (method_exists($oModuleFixer, 'setDebugOutput')) {
                    $oModuleFixer->setDebugOutput($this->getDebugOutput());
                }
                $oModuleFixer->fix($oModule);
            } else {
                $oModuleFixer->fixVersion();
                $oModuleFixer->fixExtendGently();
                $oModuleFixer->fixFiles();
                $oModuleFixer->fixTemplates();
                $oModuleFixer->fixBlocks();
                $oModuleFixer->fixSettings();
                $oModuleFixer->fixEvents();
            }


            $sCurrentVersion = $oModule->getInfo("version");
            if ($sCurrentVersion != $sVersion) {
                $this->oOutput->writeLn(
                    "[WARN] {$sModuleId} version on export" .
                    " $sVersion vs current version $sCurrentVersion please create a fresh export"
                );
                $aModuleVersions[$sModuleId] = $sCurrentVersion;
                $this->saveShopVar('aModuleVersions', $aModuleVersions, '');
            }
        }
    }

    protected function getConfigValue($aConfigValues, $name)
    {
        $TypeAndValue = $aConfigValues[$this->sNameForGeneralShopSettings][$name];
        $TypeAndValue = $this->getTypeAndValue($name, $TypeAndValue);
        $value = $TypeAndValue[1];
        return $value;
    }
    
    /**
     * @param $oModule
     * @param $aModuleExtensions
     *
     * @return
     */
    protected function collectNamespaces($oModule, $aModuleExtensions){
        $aAddModules = $oModule->getExtensions();
        
        foreach ($aAddModules as $key => $ext) {
            if(!isset($aModuleExtensions[$key])) {
                $aModuleExtensions[$key][] = $ext;
            }
        }
        
        return $aModuleExtensions;
    }
    
    /**
     * Restore module defaults and import module config
     * This will scan the module directory and add all modules (module paths).
     * This must be done before aDisabledModules is restored because this function deactivates modules.
     *
     * @param $aConfigValues
     *
     * @return null
     * @throws \Exception
     *
     * TODO: REFACTOR THIS METHOD, ADD MORE COMMENTS. (NOT READABLE)
     */
    protected function importModuleConfig(&$aConfigValues)
    {
        $allModulesConfigFromYaml = $aConfigValues['module'];

        $oConfig = $this->oConfig;
        $oxModuleList = oxNew(oxModuleList::class);
        $oxModuleList->setConfig($oConfig);

        $exclude = $this->aConfiguration['excludeFields'];
        $excludeDeep = $this->aConfiguration['excludeDeep'];
        $excludeFlat = array_flip(array_filter($exclude,'is_string'));
        /**
         * @var ModuleList $oxModuleList
         * //it is important to call this method to load new module into the shop
         */
        $aModules = $oxModuleList->getModulesFromDir($oConfig->getModulesDir());

        $aGeneralSettings = &$aConfigValues[$this->sNameForGeneralShopSettings];
        $aModuleExtensions = &$aGeneralSettings['aModules'];
        foreach ($aModules as $sModuleId => $oModule) {
            
            if ($oModule->hasExtendClass()) {
                $aModuleExtensions = $this->collectNamespaces($oModule, $aModuleExtensions);
            }

            // restore default module settings
            /** @var Module $oModule */
            $aDefaultModuleSettings = $oModule->getInfo("settings");

            // Ensure both arrays are array/not null
            $aTmp = is_null($aDefaultModuleSettings) ? array() : $aDefaultModuleSettings;
            $aDefaultModuleSettings = array();
            foreach ($aTmp as $nr => $aSetting) {
                if(!is_array($aSetting)) {
                    throw new \Exception("Error in metadata.php settings of $sModuleId in setting nr/key '$nr'. Value '" . print_r($aSetting,true) ."' is not an array!");
                }
                $aDefaultModuleSettings[$aSetting['name']] = $aSetting;
            }
            $aModuleOverride = is_null($allModulesConfigFromYaml[$sModuleId]) ? array() : $allModulesConfigFromYaml[$sModuleId];

            // merge from aModulesOverwrite into aDefaultModuleSettings
            $aMergedModuleSettings = array();
            foreach ($aDefaultModuleSettings as $sName => $aDefaultModuleSetting) {
                if (array_key_exists($sName, $aModuleOverride)) {
                    //print "module:$sModuleId $sName $aModuleOverride\n";
                    $aDefaultModuleSetting['value'] = $aModuleOverride[$sName];
                    unset($aModuleOverride[$sName]);
                }
                $aMergedModuleSettings[$sName] = $aDefaultModuleSetting;
            }
            
            foreach ($aModuleOverride as $sName => $mValue) {
                $aMergedModuleSettings[$sName] = array('value' => $mValue, 'type' => null);
            }
            // Save all that is not part of $this->aConfiguration['excludeFields'])
            foreach ($aMergedModuleSettings as $sVarName => $aVarValue) {
                // We do not want to override with default values of fields which
                // excluded from configuration export
                // as this will override those values with every config import.
                if ($excludeFlat[$sVarName] ) {
                    continue;
                }
                if ($aVarValue["type"] == 'aarr') {
                    if(isset($excludeDeep[$sVarName])) {
                        $innerExcludes = $excludeDeep[$sVarName];
                        if (!is_array( $innerExcludes )) {
                            $innerExcludes = [$innerExcludes];
                        }
                        $value         = &$aVarValue['value'];
                        $original      = $this->oConfig->getConfigParam( $sVarName );
                        if ($original) {
                            foreach ($innerExcludes as $exclude) {
                                $value[$exclude] = $original[$exclude];
                            }
                        }//else if the value is included in the dump but not avail in the database,
                        // that use it as default, that may be handy to have values that are imported once but not
                        // overwritten after that
                    }
                }


                $this->saveShopVar($sVarName, $aVarValue['value'], "module:$sModuleId", $aVarValue["type"]);
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
                $sVarType = key($mVarValue);
                $mVarValue = $mVarValue[$sVarType];
            }
        } elseif (is_array($mVarValue)) {
            $sVarType = 'arr';
        } elseif(is_bool($mVarValue)) {
            $sVarType = 'bool';
        } else {
            //deprecated check for 'bl'
            if (substr($sVarName, 0, 2) === "bl") {
                $sVarType = 'bool';
            } else {
                $sVarType = 'str';
            }
        }

        return array($sVarType, $mVarValue);
    }
    
    /**
     * @return array
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    protected function getStoredVarTypes()
    {
        $oDb = DatabaseProvider::getDb();
        $oDb->setFetchMode(\OxidEsales\EshopCommunity\Core\DatabaseProvider::FETCH_MODE_ASSOC);
        $sQ = "select CONCAT(oxvarname,'+',oxmodule) as mapkey, oxvartype from oxconfig where oxshopid = ?";
        $resultSet = $oDb->select($sQ, [$this->sShopId]);

        $allRows = $resultSet->fetchAll();
        $map = [];
        foreach($allRows as $row) {
            $map[$row['mapkey']] = $row['oxvartype'];
        }
        return $map;
    }
    public function getShopConfType($sVarName,$sSectionModule)
    {
        return $this->storedVarTypes[$sVarName.'+'.$sSectionModule];
    }
    
    /**
     * @param      $sVarName
     * @param      $mVarValue
     * @param      $sSectionModule
     * @param null $sVarType
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function saveShopVar($sVarName, $mVarValue, $sSectionModule, $sVarType = null)
    {
        
        $sShopId = $this->sShopId;
        $oConfig = $this->oConfig;

        $value = $oConfig->getShopConfVar($sVarName, $sShopId, $sSectionModule);
        $type = $this->getShopConfType($sVarName,$sSectionModule);

        if ($sVarType === null) {
            list($sVarType, $mVarValue) = $this->getTypeAndValue($sVarName, $mVarValue);
            if ($sVarType == 'bool') {
                //internal representation of bool is 1 or ''
                $value = $value ? '1' : '';
            }
        } else {
            if ($sVarType == 'bool') {
                //cleanup for some modules that have all kinds of bool representations in metadata.php
                //so we can compare that value
                $mVarValue = (($mVarValue == 'true' || $mVarValue) && $mVarValue && strcasecmp($mVarValue, "false"));
            }
        }

        if ($mVarValue !== $value || $sVarType !== $type) {
            $oConfig->saveShopConfVar(
                $sVarType,
                $sVarName,
                $mVarValue,
                $sShopId,
                $sSectionModule
            );
        }
        if(strpos($sSectionModule,'module') === 0) {
            if($existsAlsoInGlobalNameSpace = $this->getShopConfType($sVarName,'')) {
                DatabaseProvider::getDb()->execute("DELETE FROM oxconfig WHERE oxshopid = ? AND oxvarname = ? AND oxmodule = ''",[$this->sShopId,$sVarName]);
                $this->oOutput->writeLn("the config value $sVarName from module $sSectionModule was delete from global namespace");
            }
        }
    }

    protected function is_assoc_array($arr)
    {
        return is_array($arr) && (array_keys($arr) !== range(0, count($arr) - 1));
    }
    
    
    /**
     * @param $aThemes
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function importThemeConfig($aThemes)
    {
        if ($aThemes == null) {
            return;
        }
        foreach ($aThemes as $sThemeId => $aSettings) {
            $sSectionModule = "theme:$sThemeId";
            foreach ($aSettings as $sVarName => $mVarValue) {
                if(isset($mVarValue['value'])) {
                    $this->saveShopVar($sVarName, $mVarValue['value'], $sSectionModule);
                    $this->saveThemeDisplayVars($sVarName, $mVarValue, $sSectionModule);
                }
                else {
                    $this->saveShopVar($sVarName, $mVarValue, $sSectionModule);
                }
            }
        }
    }
    
    /**
     * @param $aShops
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws StandardException
     * @throws \oxfileexception
     */
    protected function runShopConfigImportForAllShops($aShops)
    {
        foreach ($aShops as $sShop => $sFileName) {
            $this->sShopId = $sShop;
            $this->storedVarTypes = $this->getStoredVarTypes();
            $this->runShopConfigImportForOneShop($sShop, $sFileName);
        }
    }
    
    /**
     * @param $aConfigValues
     *
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function restoreGeneralShopSettings($aConfigValues)
    {
        $aModuleVersions = array();
        $aGeneralSettings = $aConfigValues[$this->sNameForGeneralShopSettings];
        $sSectionModule = '';
        foreach ($aGeneralSettings as $sVarName => $mTypedVarValue) {
            list($sType, $mVarValue) = $this->getTypeAndValue($sVarName, $mTypedVarValue);
            if ($sVarName == 'aModules') {
                $aModulesTmp = array();
                foreach ($mVarValue as $sBaseClass => $aClassNames) {
                    $sAmpSeparatedClassNames = join('&', $aClassNames);
                    $aModulesTmp[$sBaseClass] = $sAmpSeparatedClassNames;
                }
                $mTypedVarValue = $aModulesTmp;
            } elseif ($sVarName == 'aModuleVersions') {
                $aModuleVersions = $mVarValue;
            }
            $this->saveShopVar($sVarName, $mTypedVarValue, $sSectionModule);
        }
        return $aModuleVersions;
    }
    
    /**
     * @param $sVarName
     * @param $mVarValue
     * @param $sModule
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function saveThemeDisplayVars($sVarName, $mVarValue, $sModule)
    {
        //exit();
        
        $oDb = DatabaseProvider::getDb();
        $sModuleQuoted = $oDb->quote($sModule);
        $sVarNameQuoted = $oDb->quote($sVarName);
        $sVarConstraintsQuoted = isset($mVarValue['constraints']) ? $oDb->quote($mVarValue['constraints']) : '\'\'';
        $sVarGroupingQuoted = isset($mVarValue['grouping']) ? $oDb->quote($mVarValue['grouping']) : '\'\'';
        $sVarPosQuoted = isset($mVarValue['pos']) ? $oDb->quote($mVarValue['pos']) : '\'\'';

        $sNewOXIDdQuoted = $oDb->quote(UtilsObject::getInstance()->generateUID());
        //$sNewOXIDdQuoted = 'toto';

        $sQ = "delete from oxconfigdisplay WHERE OXCFGVARNAME = $sVarNameQuoted and OXCFGMODULE = $sModuleQuoted";
        $oDb->execute($sQ);

        $sQ = "insert into oxconfigdisplay (oxid, oxcfgmodule, oxcfgvarname, oxgrouping, oxvarconstraint, oxpos )
               values($sNewOXIDdQuoted, $sModuleQuoted, $sVarNameQuoted, $sVarGroupingQuoted, $sVarConstraintsQuoted, $sVarPosQuoted)";
        $oDb->execute($sQ);

        //$oConfig->executeDependencyEvent($sVarName);

    }

}

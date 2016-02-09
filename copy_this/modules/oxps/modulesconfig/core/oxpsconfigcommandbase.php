<?php

use Symfony\Component\Yaml\Yaml;

abstract class OxpsConfigCommandBase
{

    /**
     * @var string The environment we are working on.
     */
    protected $sEnv = null;

    /**
     * @var oxIOutput $oOutput The output stream, where to write the configuration.
     */
    protected $oOutput;

    /*
     * @var oxiInput $oInput The input stream with arguments
     */
    protected $oInput;

    /**
     * @var array Configuration loaded from file
     */
    protected $aConfiguration;

    /**
     * @var array The configuration of the environment.
     */
    protected $aEnvConfig;

    /**
     * @var string
     */
    protected $sNameForMetaData = "Meta";

    /**
     * @var
     */
    protected $aDefaultConfig;

    /**
     * @var string
     */
    protected $sNameForGeneralShopSettings = "GeneralShopSettings";

    /**
     * @var oxOutput
     */
    protected $oDebugOutput;

    public function __construct(oxIOutput $oOutput, $oInput)
    {
        $this->oOutput = $oOutput;
        $this->oInput = $oInput;
    }

    /**
     * Sets output stream, gets environment from commandline, init configuration and set debug output stream.
     *
     * @param oxIOutput $oOutput
     * @param object $oInput
     */
    protected function init()
    {
        if ($this->oInput->hasOption(array('e', 'env'))) {
            $this->sEnv = $this->oInput->getOption(array('e', 'env'));
        } else {
            $this->sEnv = 'development';
        }
        $this->setDebugOutput();
        $this->initConfiguration();
 		if(count(array_intersect($this->aConfiguration['excludeFields'],$this->aConfiguration['envFields']))>0) {
            $this->getDebugOutput()->writeLn("CAUTION: excludeFields and envFields are not disjoint! ");
        }
    }

    /**
     * Getter for the directory where the shop configuration should be stored.
     *
     * @return string
     */
    protected function getConfigDir()
    {
        return $this->aConfiguration['dir'];
    }

    /**
     * Getter for the environment directory where environment specific config values are stored.
     *
     * @return null|string
     */
    protected function getEnviromentConfigDir()
    {
        $sDir = null;

        if ($this->sEnv) {
            $sDir = $this->aEnvConfig["dir"];
            if (!$sDir) {
                $sDir = $this->getConfigDir() . '/' . $this->sEnv;
            }
            if (!is_readable($sDir)) {
                $this->oOutput->writeLn('There is no such ' . $sDir . ' config dir. stopping');
                exit;
            }
        }

        return $sDir;
    }

    /**
     * Getter for the full file name of the shop config file.
     *
     * @return string
     */
    protected function getShopsConfigFileName()
    {
        return $this->aConfiguration['dir'] . '/shops.' . $this->getFileExt();
    }

    /**
     * Init configuration from file
     *
     * It is been done only once. It will be stored as object property
     * after first call and will return it.
     *
     * @throws oxFileException
     *
     */
    protected function initConfiguration()
    {
        $sConfigurationsDir = $this->_getConfigurationDirectoryPath();
        if ($this->aConfiguration === null) {
            $this->aConfiguration = $this->_getModuleSettings();
        }

        $aAllEnvConfigs = $this->aConfiguration['env'];
        $sFilename            = $sConfigurationsDir . 'defaultconfig' . DIRECTORY_SEPARATOR . 'defaults.yaml';
        $this->aDefaultConfig = $this->readConfigValues($sFilename, 'yaml');
        $aEnvConfig           = $aAllEnvConfigs[$this->sEnv];
        $this->aEnvConfig     = $aEnvConfig;
    }

    /*
     * @todo: is it necessary to activate the module to make the path settings?
     * @return array
     */
    protected function _getModuleSettings()
    {
        $aModulesettings = array();
        $sPathToModuleSettingsFile = $this->_getModuleSettingsFilePath();
        if(file_exists($sPathToModuleSettingsFile)){
            $aModulesettings = require $sPathToModuleSettingsFile;
        }
        return $aModulesettings;
    }

    /*
     * @return string
     *
     * @throws oxFileException
     */
    protected function _getModuleSettingsFilePath()
    {
        $sConfigurationDirectoryPath = $this->_getConfigurationDirectoryPath();
        $sModuleSettingsFilePath = $sConfigurationDirectoryPath . 'oxpsconfigmodulesettings.php';
        if(!is_file($sModuleSettingsFilePath) || !is_readable($sModuleSettingsFilePath)){
            /** @var oxFileException $oEx */
            $oEx = oxNew('oxFileException');
            $oEx->setMessage("Requested file does not exist: " . $sModuleSettingsFilePath);
            throw $oEx;
        }
        return $sModuleSettingsFilePath;
    }

    /*
     * @return string
     *
     * @throws oxFileException
     */
    protected function _getConfigurationDirectoryPath()
    {
        $oConfig = oxRegistry::getConfig();
        $sPathToThisModule = $oConfig->getModulesDir() . 'oxps' . DIRECTORY_SEPARATOR . 'modulesconfig' . DIRECTORY_SEPARATOR;
        $sRelativeConfigurationDirectoryPath = $oConfig->getConfigParam('OXPS_MODULESCONFIG_SETTING_CONFIGURATION_DIRECTORY');
        if(is_string($sRelativeConfigurationDirectoryPath)){
            $sRelativeConfigurationDirectoryPath = trim($sRelativeConfigurationDirectoryPath, '/');
        }
        $sPathToModuleSettingsFile = $sPathToThisModule . $sRelativeConfigurationDirectoryPath . DIRECTORY_SEPARATOR;
        if(!is_dir($sPathToModuleSettingsFile)){
            /** @var oxFileException $oEx */
            $oEx = oxNew("oxFileException");
            $oEx->setMessage("Requested directory does not exist: " . $sPathToModuleSettingsFile);
            throw $oEx;
        }
        return $sPathToModuleSettingsFile;
    }

    /**
     * Setter for the debug output stream.
     *
     */
    protected function setDebugOutput()
    {
        $oDebugOutput       = $this->oInput->hasOption(array('n', 'no-debug')) ? oxNew('oxNullOutput') : $this->oOutput;
        $this->oDebugOutput = $oDebugOutput;
    }

    /**
     * Getter for the debug output stream.
     *
     * @return oxIOutput
     */
    protected function getDebugOutput()
    {
        return $this->oDebugOutput;
    }

    /**
     * Getter for the file extension.
     *
     * @return string The extension of the file.
     */
    protected function getFileExt()
    {
        return $this->getExportFormat();
    }

    /**
     * Getter for the output format.
     *
     * @return string The type of output.
     */
    protected function getExportFormat()
    {
        return $this->aConfiguration['type'];
    }

    /**
     * Read configuration from file
     * It is being done only once. It will be stored as object property
     *
     * @param string $sFileName Name/path to the config file, that configure this config ex/importer
     * @param null   $sType
     *
     * @throws Exception
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     *
     * @return array|mixed
     */
    protected function readConfigValues($sFileName, $sType = null)
    {
        $this->oOutput->writeLn("Reading shop config file $sFileName");

        if ($sType == null) {
            $sType = $this->aConfiguration['type'];
        }
        $sFileContent = file_get_contents($sFileName);

        if ($sType == 'json') {
            $aResults = json_decode($sFileContent, true);
            $error    = json_last_error();
            if ($error !== JSON_ERROR_NONE) {
                throw new Exception("invalid JSON in $sFileName $error");
            }
        } elseif ($sType == 'yaml') {
            $aResults = Yaml::parse($sFileContent);
        } else {
            throw new Exception("unsuported config type" . $sType);
        }

        return $aResults;
    }
}

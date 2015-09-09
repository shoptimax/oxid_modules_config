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
        $this->initConfiguration();
        $this->setDebugOutput();
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
     */
    protected function initConfiguration()
    {
        $sCommandsDir = dirname(__DIR__) .
            DIRECTORY_SEPARATOR . 'commands';
        if ($this->aConfiguration === null) {
            $this->aConfiguration = require $sCommandsDir . DIRECTORY_SEPARATOR . 'oxpsconfig.php';
        }

        $aAllEnvConfigs = $this->aConfiguration['env'];
        $aEnvFields     = $this->aConfiguration['envFields'];
        foreach ($aEnvFields as $sExcludeField) {
            $this->aConfiguration['excludeFields'][] = $sExcludeField;
        }
        $sFilename            = $sCommandsDir . DIRECTORY_SEPARATOR . 'defaultconfig' . DIRECTORY_SEPARATOR . 'defaults.yaml';
        $this->aDefaultConfig = $this->readConfigValues($sFilename, 'yaml');
        $aEnvConfig           = $aAllEnvConfigs[$this->sEnv];
        $this->aEnvConfig     = $aEnvConfig;
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
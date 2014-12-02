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
 * Class oxpsModulesConfigRequestValidator
 * Modules configuration data validation and error handler.
 */
class oxpsModulesConfigRequestValidator extends oxSuperCfg
{

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $_aErrors = array();


    /**
     * Reset errors list to empty array.
     */
    public function resetError()
    {
        $this->_aErrors = array();
    }

    /**
     * Add an error to errors list.
     *
     * @param string $sErrorCode
     */
    public function addError($sErrorCode)
    {
        $this->_aErrors[] = $sErrorCode;
    }

    /**
     * Add multiple errors to the list.
     *
     * @param array $aErrors
     */
    public function addErrors(array $aErrors)
    {
        $this->_aErrors = array_merge($this->_aErrors, $aErrors);
    }

    /**
     * Get detected errors list.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_aErrors;
    }


    /**
     * Validate request data and collect errors if any.
     *
     * @param array $aData
     *
     * @return bool
     */
    public function validateRequestData(array $aData)
    {
        $this->resetError();

        $this->_validateModulesData($aData);
        $this->_validateSettingsData($aData);
        $this->_validateActionData($aData);

        return !$this->getErrors();
    }

    /**
     * Validate uploaded import file data and content and collect errors if any.
     *
     * @param array $aData
     *
     * @return bool
     */
    public function validateImportData(array $aData)
    {
        if (empty($aData)) {
            $this->addError('OXPS_MODULESCONFIG_ERR_NO_FILE');

            return false;
        }

        if (!empty($aData['error'])) {
            $this->_setFileUploadError($aData['error']);

            return false;
        }

        $this->_validateImportFile($aData);
        $this->_validateJsonData($aData);

        return !$this->getErrors();
    }


    /**
     * Modules data should not be empty and each module ID should be available.
     *
     * @param array $aData
     */
    protected function _validateModulesData(array $aData)
    {
        if (empty($aData['modules']) or !is_array($aData['modules'])) {
            $this->addError('OXPS_MODULESCONFIG_ERR_NO_MODULES');

            return;
        }

        /** @var oxpsModulesConfigContent $oContent */
        $oContent = oxRegistry::get('oxpsModulesConfigContent');
        $aValidModules = $oContent->getModulesList();

        foreach ($aData['modules'] as $sModule) {
            if (!array_key_exists($sModule, $aValidModules)) {
                $this->addError('OXPS_MODULESCONFIG_ERR_INVALID_MODULE');
                break;
            }
        }
    }

    /**
     * Settings data should not be empty and each setting name should be available.
     *
     * @param array $aData
     */
    protected function _validateSettingsData(array $aData)
    {
        if (empty($aData['settings']) or !is_array($aData['settings'])) {
            $this->addError('OXPS_MODULESCONFIG_ERR_NO_SETTINGS');

            return;
        }

        /** @var oxpsModulesConfigContent $oContent */
        $oContent = oxRegistry::get('oxpsModulesConfigContent');
        $aValidSettings = $oContent->getSettingsList();

        foreach ($aData['settings'] as $sSettings) {
            if (!array_key_exists($sSettings, $aValidSettings)) {
                $this->addError('OXPS_MODULESCONFIG_ERR_INVALID_SETTING');
                break;
            }
        }
    }

    /**
     * Action name should be not empty and among available actions.
     *
     * @param array $aData
     */
    protected function _validateActionData(array $aData)
    {
        if (empty($aData['action']) or !in_array($aData['action'], array('export', 'backup', 'import'))) {
            $this->addError('OXPS_MODULESCONFIG_ERR_INVALID_ACTION');
        }
    }

    /**
     * Set error by file upload error code.
     *
     * @param int $iFileUploadErrorCode
     */
    protected function _setFileUploadError($iFileUploadErrorCode)
    {
        $aFileUploadErrors = array(
            UPLOAD_ERR_INI_SIZE   => 'OXPS_MODULESCONFIG_ERR_FILE_SIZE',
            UPLOAD_ERR_FORM_SIZE  => 'OXPS_MODULESCONFIG_ERR_FILE_SIZE',
            UPLOAD_ERR_PARTIAL    => 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR',
            UPLOAD_ERR_NO_FILE    => 'OXPS_MODULESCONFIG_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR => 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR',
            UPLOAD_ERR_CANT_WRITE => 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR',
            UPLOAD_ERR_EXTENSION  => 'OXPS_MODULESCONFIG_ERR_FILE_TYPE',
        );

        $sErrorCode = array_key_exists($iFileUploadErrorCode, $aFileUploadErrors)
            ? $aFileUploadErrors[$iFileUploadErrorCode]
            : 'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR';

        $this->addError($sErrorCode);
    }

    /**
     * Validate uploaded file to be of JSON type and readable.
     *
     * @param array $aFileData
     */
    protected function _validateImportFile(array $aFileData)
    {
        if (empty($aFileData['type']) or
            !in_array($aFileData['type'], array('application/json', 'application/octet-stream'))
        ) {
            $this->addError('OXPS_MODULESCONFIG_ERR_FILE_TYPE');
        } elseif (empty($aFileData['tmp_name']) or !$this->_isReadableFile($aFileData['tmp_name'])) {
            $this->addError('OXPS_MODULESCONFIG_ERR_CANNOT_READ');
        }
    }

    /**
     * Check if there is a readable file under a path.
     *
     * @codeCoverageIgnore
     *
     * @param string $aFilePath
     *
     * @return bool
     */
    protected function _isReadableFile($aFilePath)
    {
        return (is_file($aFilePath) and is_readable($aFilePath));
    }

    /**
     * Set JSON import data from file and check modules configuration data for errors.
     * Checks it only if there are no other errors.
     *
     * @param array $aFileData
     */
    protected function _validateJsonData(array $aFileData)
    {
        if (!$this->getErrors()) {

            /** @var oxpsModulesConfigTransfer $oModulesConfig */
            $oModulesConfig = oxNew('oxpsModulesConfigTransfer');
            $oModulesConfig->setImportDataFromFile($aFileData);

            $this->addErrors((array) $oModulesConfig->getImportDataValidationErrors());
        }
    }
}

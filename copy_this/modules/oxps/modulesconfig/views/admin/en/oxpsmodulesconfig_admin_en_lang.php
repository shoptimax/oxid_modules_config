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

$sLangName = 'English';

$aLang = array(
    'charset'                                => 'UTF-8',
    'oxpsmodulesconfig'                      => 'Module Configuration Im-/Exporter',

    // Common dashboard translations
    'OXPS_MODULESCONFIG_DASHBOARD'           => 'Modules Configuration Export and Import',
    'OXPS_MODULESCONFIG_NO_MODULES'          => 'There are no modules available for configuration export or import.',

    // Form translations
    'OXPS_MODULESCONFIG_MODULES'             => 'Select modules for export or import',
    'OXPS_MODULESCONFIG_MODULES_HELP'        => 'Hold "Ctrl" button and click to select module that should be ' .
                                                'evolved in configuration export or import action.',
    'OXPS_MODULESCONFIG_ALL'                 => 'Select All',
    'OXPS_MODULESCONFIG_NONE'                => 'Deselect All',
    'OXPS_MODULESCONFIG_SETTINGS'            => 'Choose settings to export or import',
    'OXPS_MODULESCONFIG_SETTINGS_HELP'       => 'Deselect setting types that should not be evolved in configuration ' .
                                                'export or import action.',
    'OXPS_MODULESCONFIG_EXPORT'              => 'Export',
    'OXPS_MODULESCONFIG_EXPORT_HELP'         => 'All checked settings of selected modules will be exported to a ' .
                                                'JSON file for download.',
    'OXPS_MODULESCONFIG_BACKUP'              => 'Backup',
    'OXPS_MODULESCONFIG_BACKUP_HELP'         => 'All checked settings of selected modules will be exported to a ' .
                                                'JSON file and stored in file system, under "export/modules_config/".',
    'OXPS_MODULESCONFIG_FILE'                => 'Choose a JSON file to import',
    'OXPS_MODULESCONFIG_FILE_HELP'           => 'It should ne a valid JSON file with OXID modules configuration data.',
    'OXPS_MODULESCONFIG_IMPORT'              => 'Import',
    'OXPS_MODULESCONFIG_IMPORT_HELP'         => 'All checked settings of selected modules will be overwritten by ' .
                                                'corresponding values from imported JSON file. ' .
                                                'An automatic backup will be done before the import.',

    // Module settings translations
    'OXPS_MODULESCONFIG_SETTING_VERSION'     => 'Versions',
    'OXPS_MODULESCONFIG_SETTING_EXTEND'      => 'Extended classes',
    'OXPS_MODULESCONFIG_SETTING_FILES'       => 'Module classes',
    'OXPS_MODULESCONFIG_SETTING_TEMPLATES'   => 'Templates',
    'OXPS_MODULESCONFIG_SETTING_BLOCKS'      => 'Blocks',
    'OXPS_MODULESCONFIG_SETTING_SETTINGS'    => 'Settings',
    'OXPS_MODULESCONFIG_SETTING_EVENTS'      => 'Events',

    // Errors
    'OXPS_MODULESCONFIG_ERR_NO_MODULES'      => 'Please select at least one module.',
    'OXPS_MODULESCONFIG_ERR_INVALID_MODULE'  => 'Invalid module selected! Please refresh page and try again.',
    'OXPS_MODULESCONFIG_ERR_NO_SETTINGS'     => 'Please check at least one setting type.',
    'OXPS_MODULESCONFIG_ERR_INVALID_SETTING' => 'Invalid setting checked! Please refresh page and try again.',
    'OXPS_MODULESCONFIG_ERR_INVALID_ACTION'  => 'Invalid request, please try again.',
    'OXPS_MODULESCONFIG_ERR_EXPORT_FAILED'   => 'Export operation failed. Please try again later.',
    'OXPS_MODULESCONFIG_ERR_BACKUP_FAILED'   => 'Backup operation failed. ' .
                                                'Please make sure eShop folder "export/" is writable and try again.',

    // File upload errors
    'OXPS_MODULESCONFIG_ERR_NO_FILE'         => 'Please select a valid JSON file for modules configuration import.',
    'OXPS_MODULESCONFIG_ERR_FILE_SIZE'       => 'Uploaded file size is too big. Please contact system administrator.',
    'OXPS_MODULESCONFIG_ERR_UPLOAD_ERROR'    => 'File upload failed. Please try again later.',
    'OXPS_MODULESCONFIG_ERR_FILE_TYPE'       => 'File format is invalid. Please select a valid JSON file ' .
                                                'containing modules configuration data.',
    'OXPS_MODULESCONFIG_ERR_CANNOT_READ'     => 'Uploaded file cannot be read. Please select a valid JSON file ' .
                                                'containing modules configuration data.',

    // JSON data errors
    'OXPS_MODULESCONFIG_ERR_EMPTY_DATA'      => 'Import data was not found. Please select a valid JSON file ' .
                                                'containing modules configuration data.',
    'OXPS_MODULESCONFIG_ERR_INVALID_FORMAT'  => 'Import data structure is invalid. Please select a valid JSON file ' .
                                                'containing modules configuration data.',
    'OXPS_MODULESCONFIG_ERR_SHOP_VERSION'    => 'Import data rejected because shop version does not match.',
    'OXPS_MODULESCONFIG_ERR_SHOP_EDITION'    => 'Import data rejected because shop edition does not match.',
    'OXPS_MODULESCONFIG_ERR_WRONG_SUBSHOP'   => 'Import data rejected because sub-shop ID does not match.',

    // Messages
    'OXPS_MODULESCONFIG_MSG_BACKUP_SUCCESS'  => 'Backup was created successfully. ' .
                                                'File was saved to eShop folder "export/modules_config/".',
    'OXPS_MODULESCONFIG_MSG_IMPORT_SUCCESS'  => 'Modules configuration was imported successfully. ',
);

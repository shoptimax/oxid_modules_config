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
    'charset'                              => 'UTF-8',
    'oxpsmodulesconfig'                    => 'Modules Config',

    // Common dashboard translations
    'OXPS_MODULESCONFIG_DASHBOARD'         => 'Modules Configuration Export and Import',
    'OXPS_MODULESCONFIG_NO_MODULES'        => 'There are no modules available for configuration export or import.',

    // Form translations
    'OXPS_MODULESCONFIG_MODULES'           => 'Select modules for export or import',
    'OXPS_MODULESCONFIG_MODULES_HELP'      => 'Hold "Ctrl" button and click to select module that should be enrolled in configuration export or import action.',
    'OXPS_MODULESCONFIG_ALL'               => 'Select All',
    'OXPS_MODULESCONFIG_NONE'              => 'Deselect All',
    'OXPS_MODULESCONFIG_SETTINGS'          => 'Choose settings to export or import',
    'OXPS_MODULESCONFIG_SETTINGS_HELP'     => 'Deselect setting types that should not be evolved in configuration export or import action.',
    'OXPS_MODULESCONFIG_EXPORT'            => 'Export',
    'OXPS_MODULESCONFIG_EXPORT_HELP'       => 'All checked settings of selected modules will be exported to a JSON file for download.',
    'OXPS_MODULESCONFIG_BACKUP'            => 'Backup',
    'OXPS_MODULESCONFIG_BACKUP_HELP'       => 'All checked settings of selected modules will be exported to a JSON file and stored in file system.',
    'OXPS_MODULESCONFIG_FILE'              => 'Choose a JSON file to import',
    'OXPS_MODULESCONFIG_FILE_HELP'         => 'It should ne a valid JSON file with OXID modules configuration data.',
    'OXPS_MODULESCONFIG_IMPORT'            => 'Import',
    'OXPS_MODULESCONFIG_IMPORT_HELP'       => 'All checked settings of selected modules will be overwritten by corresponding values from imported JSON file. An automatic backup will be done before the import.',

    // Module settigns translations
    'OXPS_MODULESCONFIG_SETTING_METADATA'  => 'Main module data',
    'OXPS_MODULESCONFIG_SETTING_EXTEND'    => 'Extended classes',
    'OXPS_MODULESCONFIG_SETTING_FILES'     => 'Module classes',
    'OXPS_MODULESCONFIG_SETTING_TEMPLATES' => 'Templates',
    'OXPS_MODULESCONFIG_SETTING_BLOCKS'    => 'Blocks',
    'OXPS_MODULESCONFIG_SETTING_SETTINGS'  => 'Settings',
    'OXPS_MODULESCONFIG_SETTING_EVENTS'    => 'Events',
);

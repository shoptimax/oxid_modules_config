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
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'oxpsmodulesconfig',
    'title'       => 'OXID Module Configuration Im-/Exporter',
    'description' => array(
        'de' => '[TR - Tools to export, backup and import OXID eShop modules configuration data.]',
        'en' => 'Tools to export, backup and import OXID eShop modules configuration data.',
    ),
    'thumbnail'   => 'out/pictures/oxpsmodulesconfig.png',
    'version'     => '0.2.0',
    'author'      => 'OXID Professional Services',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => array(),
    'files'       => array(
        'admin_oxpsmodulesconfigdashboard'  => 'oxps/modulesconfig/controllers/admin/admin_oxpsmodulesconfigdashboard.php',
        'oxpsmodulesconfigjsonvalidator'    => 'oxps/modulesconfig/core/oxpsmodulesconfigjsonvalidator.php',
        'oxpsmodulesconfigmodule'           => 'oxps/modulesconfig/core/oxpsmodulesconfigmodule.php',
        'oxpsmodulesconfigrequestvalidator' => 'oxps/modulesconfig/core/oxpsmodulesconfigrequestvalidator.php',
        'oxpsmodulesconfigtransfer'         => 'oxps/modulesconfig/core/oxpsmodulesconfigtransfer.php',
        'oxpsmodulesconfigcontent'          => 'oxps/modulesconfig/models/oxpsmodulesconfigcontent.php',
        'oxpsmodulesconfigstorage'          => 'oxps/modulesconfig/models/oxpsmodulesconfigstorage.php',
    ),
    'templates'   => array(
        'admin_oxpsmodulesconfigdashboard.tpl' => 'oxps/modulesconfig/views/admin/admin_oxpsmodulesconfigdashboard.tpl',
    ),
    'events'      => array(
        'onActivate'   => 'oxpsModulesConfigModule::onActivate',
        'onDeactivate' => 'oxpsModulesConfigModule::onDeactivate',
    ),
);

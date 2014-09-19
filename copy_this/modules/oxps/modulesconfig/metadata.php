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
    'title'       => array(
        'de' => '[TR - OXPS Modules Config]',
        'en' => 'OXPS Modules Config',
    ),
    'description' => array(
        'de' => '[TR - Modules configuration export and import tools]',
        'en' => 'Modules configuration export and import tools',
    ),
    'thumbnail'   => 'out/pictures/picture.png',
    'version'     => '0.0.1',
    'author'      => 'OXID Professional Services',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => array(),
    'files'       => array(
        'admin_oxpsmodulesconfigdashboard' => 'oxps/modulesconfig/controllers/admin/admin_oxpsmodulesconfigdashboard.php',
        'oxpsmodulesconfigmodule'          => 'oxps/modulesconfig/core/oxpsmodulesconfigmodule.php',
        'oxpsmodulesconfigtransfer'        => 'oxps/modulesconfig/core/oxpsmodulesconfigtransfer.php',
    ),
    'templates'   => array(
        'admin_oxpsmodulesconfigdashboard.tpl' => 'oxps/modulesconfig/views/admin/admin_oxpsmodulesconfigdashboard.tpl',
    ),
    'blocks'      => array(),
    'settings'    => array(),
    'events'      => array(
        'onActivate'   => 'oxpsModulesConfigModule::onActivate',
        'onDeactivate' => 'oxpsModulesConfigModule::onDeactivate',
    ),
);

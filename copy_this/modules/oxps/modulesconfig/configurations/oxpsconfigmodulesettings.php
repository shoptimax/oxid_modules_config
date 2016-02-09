<?php

return array(
    'dir' => getShopBasePath() . '/modules/oxps/modulesconfig/configurations',
    'type' => 'yaml',
    'executeModuleActivationEvents' => false,
    //config fields that should never ever go to the config export
    //because they are generated or sensible in any way
    //TODO: document them
    'excludeFields' => array(
        'aServersData',
        'blEnableIntangibleProdAgreement',
        'blShowTSCODMessage',
        'blShowTSInternationalFeesMessage',
        'iOlcSuccess',
        'sBackTag',
        'sClusterId',
        'sOnlineLicenseCheckTime',
        'sOnlineLicenseNextCheckTime',
        'sParcelService',
    ),
    //environment specific fields
    'envFields' => array(
        'oxps123TvMISEnvironment', //environment dependent
        'oxps123PaymentsTesting',
        'aSerials', //oxid serial numbers. Must be different on live system.
        'OXSERIAL', // generated single serial number from all aSerials
        'sMallShopURL',
        'sMallSSLShopURL',
        'blCheckTemplates', //sets if templates should be recompililed on change good in develop env

        /* oxshops table */
        'OXPRODUCTIVE',
        
        /* Paypal development settings */
        'blOEPayPalSandboxMode',
        'blPayPalLoggerEnabled',
        'sOEPayPalPassword',
        'sOEPayPalSignature',
        'sOEPayPalUserEmail',
        'sOEPayPalUsername',
        'sOEPayPalSandboxPassword',
        'sOEPayPalSandboxSignature',
        'sOEPayPalSandboxUserEmail',
        'sOEPayPalSandboxUsername',
        /* END Paypal development settings END */

        /* Factfinder development settings */
        'swFF.authentication.password',
        'swFF.authentication.username',
        'swFF.context',
        /* END Factfinder END */

        /* contenido cms */
        'o2c_sUsername',
        'o2c_sUserPassword',
        'o2c_sSoapServerAddress',
    ),
    'env' => array(
        //'develop' => array(
            //'dir' => dirname(__DIR__) . '/../../config/$env' is default
        //),

        /* map other environments to existing ones */
        'development' => array(
            'dir' => getShopBasePath() . '/modules/oxps/modulesconfig/configurations/development',
        ),
        'merge-request' => array(
            'dir' => getShopBasePath() . '/modules/oxps/modulesconfig/configurations/integration',
        ),
        'testing' => array(
            'dir' => getShopBasePath() . '/modules/oxps/modulesconfig/configurations/testing',
        )
    ),
);

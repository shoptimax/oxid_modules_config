<?php

return [
    'dir' => dirname(__DIR__) . '/../../config',
    'type' => 'yaml',
    'executeModuleActivationEvents' => false,
    //config fields that should never ever go to the config export
    //because they are generated or sensible in any way
    //TODO: document them
    'excludeFields' => [
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
    ],
    //environment specific fields
    'envFields' => [
        'oxps123TvMISEnvironment', //environment dependent
        'oxps123PaymentsTesting',
        'aSerials', //oxid serial number. Must be different on live system.
        'sMallShopURL',
        'sMallSSLShopURL',
        /* Paypal development settings */
        'blOEPayPalSandboxMode',
        'blPayPalLoggerEnabled',
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
        'o2c_sSoapServerAddress',
    ],
    'env' => [
        //'develop' => [
            //'dir' => dirname(__DIR__) . '/../../config/$env' is default
        //],

        /* map other environments to existing ones */
        'develop' => [
            'dir' => dirname(__DIR__) . '/../../config/development',
        ],
        'merge-request' => [
            'dir' => dirname(__DIR__) . '/../../config/integration',
        ],
        'testing' => [
            'dir' => dirname(__DIR__) . '/../../config/integration',
        ]
    ],
];

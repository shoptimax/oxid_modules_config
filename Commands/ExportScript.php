<?php
    require __DIR__ . '/../../../../bootstrap.php';
    
    $oConfigExport = oxNew(Oxps\ModulesConfig\Core\ConfigExport::class, null, "config:export");
    $oConfigExport->executeConsoleCommand();
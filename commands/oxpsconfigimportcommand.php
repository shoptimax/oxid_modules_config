<?php
/**
 * This file is part of OXID Console.
 *
 * OXID Console is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID Console is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID Console.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 */


/**
 * Class OxpsConfigImportCommandBase
 */
class OxpsConfigImportCommand extends oxConsoleCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('config:import');
        $this->setDescription('Import shop config');
    }

    /**
     * {@inheritdoc}
     */
    public function help(oxIOutput $oOutput)
    {
        $oOutput->writeLn('Usage: config:import [options]');
        $oOutput->writeLn();
        $oOutput->writeLn('This command imports shop config');
        $oOutput->writeLn();
        $oOutput->writeLn('Available options:');
        $oOutput->writeLn('  -n, --no-debug     No debug output');
        $oOutput->writeLn('  --env=ENVIRONMENT  Environment');
        //TODO: $oOutput->writeLn('  --shop=SHOPID      Shop');
    }

    /**
     * Execute current command
     *
     * @param oxIOutput $oOutput
     */
    public function execute(oxIOutput $oOutput)
    {
        $oInput        = $this->getInput();
        if (!class_exists('oxpsModulesConfigConfigImport')) {
            $oOutput->writeLn('Config importer is not active trying to activate...');
            $oModuleInstaller = oxRegistry::get('oxModuleInstaller');
            $oxModuleList = oxNew('oxModuleList');
            $oxModuleList->getModulesFromDir(oxRegistry::getConfig()->getModulesDir());
            $aModules = $oxModuleList->getList();
            /** @var oxModule $oModule */
            $oModule = $aModules['oxpsmodulesconfig'];
            $oModuleInstaller->activate($oModule);

            //workaround for issue in oxid see https://github.com/OXID-eSales/oxideshop_ce/pull/413
            $utilsObject = oxUtilsObject::getInstance();
            $utilsObject->setModuleVar('aModuleFiles',null);
        }
        $oConfigExport = oxNew('oxpsModulesConfigConfigImport', $oOutput, $oInput);
        $oConfigExport->executeConsoleCommand();
    }

}

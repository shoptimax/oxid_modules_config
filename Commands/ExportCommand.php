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
 * @copyright (C) OXID eSales AG 2003-2018
 */


namespace Oxps\ModulesConfig\Commands;


use Oxps\ModulesConfig\Core\ConfigExport;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class ExportCommand
 */
class ExportCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('config:export')
            ->setDescription('Export shop config')
            // if you want to add more option here, copy them in ConfigExport class
            ->addOption(
                'no-debug',
                null,//can not use n
                InputOption::VALUE_NONE,
                'No debug ouput',
                null
            )
            ->addOption(
                'env',
                null,
                InputOption::VALUE_OPTIONAL,
                'Environment',
                null
            )
            ;
    }
    
    /**
     * Execute current command
     *
     * @param InputInterface  $input OutputInterface $output
     * @param OutputInterface $output
     *
     * @throws \Oxps\ModulesConfig\Core\Exception
     * @throws \oxfileexception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $oConfigExport = new ConfigExport();
        $oConfigExport->initialize($input, $output);
        $oConfigExport->execute($input, $output);
    }

}

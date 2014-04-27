<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CreateBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitHalloDevelCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('cmf:create:init-hallo-devel')
        ;
    }

    /**
     * This will clone the hallo repository into Resources/public/vendor
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = null;
        $output = array();
        $dir = getcwd();
        chdir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'vendor');
        exec('git clone https://github.com/bergie/hallo.git', $output, $status);
        chdir($dir);
        if ($status) {
            die("Running git submodule sync failed with $status\n");
        }
        if ($status) {
            die("Running git submodule --init --recursive failed with $status\n");
        }
    }
}

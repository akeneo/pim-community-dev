<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\Command;

use Pim\Bundle\InstallerBundle\Command\DatabaseCommand as BaseDatabaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override the database command to be able to clean category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DatabaseCommand extends BaseDatabaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        parent::launchCommands($input, $output);

        $this->commandExecutor->runCommand('pimee:installer:clean-category-accesses');

        return $this;
    }
}

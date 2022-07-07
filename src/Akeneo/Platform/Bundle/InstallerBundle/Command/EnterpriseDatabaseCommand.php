<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override the database command to be able to clean category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class EnterpriseDatabaseCommand extends DatabaseCommand
{
    protected static $defaultName = 'pim:installer:db';
    protected static $defaultDescription = 'Prepare database and load fixtures';

    /**
     * {@inheritdoc}
     */
    protected function launchCommands(): void
    {
        parent::launchCommands();

        $this->commandExecutor->runCommand('pimee:installer:clean-category-accesses');
        $this->commandExecutor->runCommand('pimee:installer:clean-attribute-group-accesses');
    }
}

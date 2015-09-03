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

use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\InstallerBundle\Command\LoadDataFixturesDoctrineCommand as BaseLoadDataFixturesDoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override of the LoadDataFixturesDoctrineCommand.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class LoadDataFixturesDoctrineCommand extends BaseLoadDataFixturesDoctrineCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getFixtures(array $paths)
    {
        $fixtures = parent::getFixtures($paths);

        // Dirty fix in order to prevent the pim fixture to be loaded as it is overridden in PimEnterprise
        foreach ($fixtures as $key => $fixture) {
            if (ClassUtils::getClass($fixture) === 'Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadUserData') {
                unset($fixtures[$key]);
            }
        }

        return $fixtures;
    }
}

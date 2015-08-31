<?php

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

        foreach ($fixtures as $key => $fixture) {
            if (ClassUtils::getClass($fixture) === 'Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadUserData') {
                unset($fixtures[$key]);
            }
        }

        return $fixtures;
    }
}

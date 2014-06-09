<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use InvalidArgumentException;
use Pim\Bundle\InstallerBundle\DataFixtures\Executor\ORMExecutor;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand as BaseLoadDataFixturesDoctrineCommand;

/**
 * Load data fixtures from bundles.
 * Overriding the LoadDataFixturesDoctrineCommand to be able to use our own ORMExecutor.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadDataFixturesDoctrineCommand extends BaseLoadDataFixturesDoctrineCommand
{
    /**
     * @var EntityManagerInterface entity manager
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $doctrine \Doctrine\Common\Persistence\ManagerRegistry */
        $doctrine = $this->getContainer()->get('doctrine');
        $this->em = $doctrine->getManager($input->getOption('em'));

        if ($input->isInteractive() && !$input->getOption('append')) {
            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation(
                $output,
                '<question>Careful, database will be purged. Do you want to continue Y/N ?</question>',
                false
            )) {
                return;
            }
        }

        $paths = $this->getFixturePaths($input->getOption('fixtures'));
        $fixtures = $this->getFixtures($paths);

        $this->purgeAndExecute(
            $output,
            $fixtures,
            $input->getOption('purge-with-truncate'),
            $input->getOption('append')
        );
    }

    /**
     * Get the paths of the fixtures
     *
     * @param $fixturesOption
     *
     * @return array
     */
    protected function getFixturePaths($fixturesOption)
    {
        if ($fixturesOption) {
            $paths = is_array($fixturesOption) ? $fixturesOption : array($fixturesOption);
        } else {
            $paths = [];
            foreach ($this->getApplication()->getKernel()->getBundles() as $bundle) {
                $paths[] = $bundle->getPath().'/DataFixtures/ORM';
            }
        }

        return $paths;
    }

    /**
     * Get the fixtures to load
     *
     * @param $paths
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getFixtures($paths)
    {
        $loader = new DataFixturesLoader($this->getContainer());
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            }
        }
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        return $fixtures;
    }

    /**
     * Purge database and execute fixtures loading
     *
     * @param OutputInterface $output
     * @param array           $fixtures
     * @param                 $purgeWithTruncate
     * @param                 $appendFixtures
     */
    protected function purgeAndExecute(OutputInterface $output, array $fixtures, $purgeWithTruncate, $appendFixtures)
    {
        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode($purgeWithTruncate ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);

        // we use here our own Pim\Bundle\InstallerBundle\DataFixtures\Executor\ORMExecutor\ORMExecutor
        $executor = new ORMExecutor($this->em, $purger);
        $executor->setLogger(
            function ($message) use ($output) {
                $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
            }
        );
        $executor->execute($fixtures, $appendFixtures);
    }
}

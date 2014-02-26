<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use Pim\Bundle\InstallerBundle\CommandExecutor;

/**
 * Database preparing command
 * - creates database
 * - updates schema
 * - loads fixtures
 * - launches other command for database calculations (search index, completeness calculation)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabaseCommand extends ContainerAwareCommand
{
    /**
     * @staticvar string
     */
    const LOAD_ALL    = 'all';
    const LOAD_ORO    = 'OroPlatform';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:db')
            ->setDescription('Prepare database and load fixtures')
            ->addOption(
                'fixtures',
                null,
                InputOption::VALUE_REQUIRED,
                'Determines fixtures to load (can be just OroPlatform or all)',
                self::LOAD_ALL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor = new CommandExecutor(
            $input,
            $output,
            $this->getApplication()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Prepare database schema</info>');

        $this->commandExecutor
            ->runCommand('doctrine:database:drop', array('--force' => true))
            ->runCommand('doctrine:database:create');

        // Needs to close connection if always open
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        if ($connection->isConnected()) {
            $connection->close();
        }

        $this->commandExecutor
            ->runCommand('doctrine:schema:create')
            ->runCommand('oro:entity-config:init')
            ->runCommand('oro:entity-extend:init')
            ->runCommand(
                'oro:entity-extend:update-config'
            )
            ->runCommand(
                'doctrine:schema:update',
                array('--force' => true, '--no-interaction' => true)
            );

        $this
            ->loadFixturesStep($input, $output)
            ->launchCommands($input, $output);

        return $this;
    }

    /**
     * Step where fixtures are loaded
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function loadFixturesStep(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('env') === 'behat') {
            $input->setOption('fixtures', self::LOAD_ORO);
        }

        $output->writeln('<info>Load fixtures.</info>');

        $params = array(
                '--no-interaction' => true,
                '--append' => true
            )
            + $this->getFixturesList($input->getOption('fixtures'));

        $this->commandExecutor->runCommand('doctrine:fixtures:load', $params);

        $output->writeln('');

        return $this;
    }

    /**
     * Get fixtures to load list
     *
     * @param string $fixtureOpt
     *
     * @return array
     */
    protected function getFixturesList($fixtureOpt)
    {
        if ($fixtureOpt === self::LOAD_ORO) {
            $bundles = $this->getContainer()->getParameter('kernel.bundles');

            $basePath = realpath($this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR .'..');
            $finder = new Finder();

            foreach ($bundles as $bundleName => $bundleNamespace) {
                if (strpos($bundleNamespace, 'Oro\\') === 0) {
                    $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);
                    $finder->in($bundle->getPath());
                }
            }
            // Oro User Bundle overriden by Pim User Bundle, but we still need the data fixtures inside OroUserBundle
            $finder->in($basePath."/vendor/oro/platform/src/Oro/Bundle/UserBundle");
            $directories = $finder
                ->path('/^DataFixtures$/')
                ->directories();

            $oroFixtures = array();
            foreach ($directories as $directory) {
                $oroFixtures[] = $directory->getPathName();
            }

            return array('--fixtures' => $oroFixtures);
        }

        return array();
    }

    /**
     * Launchs all commands needed after fixtures loading
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor
            ->runCommand('oro:search:create-index')
            ->runCommand(
                'pim:search:reindex',
                array('locale' => $this->getContainer()->getParameter('locale'))
            )
            ->runCommand('pim:versioning:refresh')
            ->runCommand('pim:completeness:calculate');

        return $this;
    }
}

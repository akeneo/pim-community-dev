<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\ProcessBuilder;

use Symfony\Component\Process\PhpExecutableFinder;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\InstallerBundle\Command\InstallCommand as OroInstallCommand;

/**
 * Override OroInstaller command to add PIM custom rules
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends OroInstallCommand
{
    const APP_NAME    = 'Akeneo PIM';

    const TASK_ALL    = 'all';
    const TASK_ASSETS = 'assets';
    const TASK_CHECK  = 'check';
    const TASK_DB     = 'db';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('pim:install')
            ->setDescription(static::APP_NAME .' Application Installer.')
            ->addOption(
                'task',
                null,
                InputOption::VALUE_REQUIRED,
                'Determines tasks called for installation (can be all, check, db or assets)',
                self::TASK_ALL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getOption('task');
        if ($task === self::TASK_DB || $task === self::TASK_ALL) {

            $this
                ->runCommand('doctrine:database:drop', $input, $output, array('--force' => true, '--full-database'))
                ->runCommand('doctrine:database:create', $input, $output);

            parent::setupStep($input, $output);

            $this
                // @TODO: Replace en_US by locale parameter
                ->runCommand('pim:search:reindex', $input, $output, array('locale' => 'en_US'))
                ->runCommand('pim:versioning:refresh', $input, $output)
                ->runCommand('doctrine:query:sql', $input, $output, array('sql' => '"ANALYZE TABLE pim_product_value;"'))
                ->runCommand('doctrine:query:sql', $input, $output, array('sql' => '"ANALYZE TABLE pim_icecatdemo_product_value;"'))
                ->runCommand('pim:completeness:calculate', $input, $output);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function finalStep(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getOption('task');
        if ($task === self::TASK_ASSETS || $task === self::TASK_ALL) {
            $this->oroFinalStep($input, $output);
        }

        return $this;
    }

    /**
     * Override parent class finalStep method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return \Pim\Bundle\InstallerBundle\Command\InstallCommand
     */
    private function oroFinalStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $input->setInteractive(false);

        $this
            ->runCommand('fos:js-routing:dump', $input, $output, array('--target' => 'web/js/routes.js'))
            ->runCommand('oro:navigation:init', $input, $output)
            ->runCommand('assets:install', $input, $output)
            ->runCommand('assetic:dump', $input, $output)
            ->runCommand('oro:assetic:dump', $input, $output)
            ->runCommand('oro:translation:dump', $input, $output)
            ->runCommand('oro:localization:dump', $input, $output);

        $output->writeln('');

        return $this;
    }

    /**
     * Update installed flag in parameters.yml and clear it from DI container
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function updateInstalledFlag(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Updating installed flag</info>');

        $dumper = $this->getContainer()->get('oro_installer.yaml_persister');
        $params = $dumper->parse();
        $params['system']['installed'] = date('c');
        $dumper->dump($params);

        $this->runCommand('cache:clear', $input, $output);
        $output->writeln('');

        return $this;
    }
}

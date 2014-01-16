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
     * {@inheritDoc}
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
            parent::finalStep($input, $output);
        }

        return $this;
    }
}

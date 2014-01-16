<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

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
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('pim:install')
            ->setDescription('Akeneo PIM Application Installer.')
            ->addOption('user-name', null, InputOption::VALUE_OPTIONAL, 'User name')
            ->addOption('user-email', null, InputOption::VALUE_OPTIONAL, 'User email')
            ->addOption('user-firstname', null, InputOption::VALUE_OPTIONAL, 'User first name')
            ->addOption('user-lastname', null, InputOption::VALUE_OPTIONAL, 'User last name')
            ->addOption('user-password', null, InputOption::VALUE_OPTIONAL, 'User password')
            ->addOption(
                'sample-data',
                null,
                InputOption::VALUE_OPTIONAL,
                'Determines whether sample data need to be loaded or not'
            );
    }

    /**
     * Override to add custom commands
     *
     * {@inheritDoc}
     */
    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        parent::setupStep($input, $output);

        $this
            ->runCommand('pim:search:reindex', $input, $output, array('locale' => 'en_US'))
            ->runCommand('pim:versioning:refresh', $input, $output)
            ->runCommand('doctrine:query:sql', $input, $output, array('sql' => '"ANALYZE TABLE pim_product_value"'))
            ->runCommand('doctrine:query:sql', $input, $output, array('sql' => '"ANALYZE TABLE pim_icecatdemo_product_value"'))
            ->runCommand('pim:completeness:calculate', $input, $output);

        return $this;
    }

    /**
     * Launches a command.
     * If '--process-isolation' parameter is specified the command will be launched as a separate process.
     * In this case you can parameter '--process-timeout' to set the process timeout
     * in seconds. Default timeout is 60 seconds.
     *
     * @param string          $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $params
     *
     * @return InstallCommand
     */
    private function runCommand($command, InputInterface $input, OutputInterface $output, $params = array())
    {
        $params = array_merge(
            array(
                'command'    => $command,
                '--no-debug' => true,
            ),
            $params
        );
        if ($input->hasOption('env') && $input->getOption('env') !== 'dev') {
            $params['--env'] = $input->getOption('env');
        }

        if (array_key_exists('--process-isolation', $params)) {
            unset($params['--process-isolation']);
            $phpFinder = new PhpExecutableFinder();
            $php = $phpFinder->find();
            $pb = new ProcessBuilder();
            $pb
                ->add($php)
                ->add($_SERVER['argv'][0]);

            if (array_key_exists('--process-timeout', $params)) {
                $pb->setTimeout($params['--process-timeout']);
                unset($params['--process-timeout']);
            }

            foreach ($params as $param => $val) {
                if ($param && '-' === $param[0]) {
                    if ($val === true) {
                        $pb->add($param);
                    } else {
                        $pb->add($param . '=' . $val);
                    }
                } else {
                    $pb->add($val);
                }
            }

            $process = $pb
                ->inheritEnvironmentVariables(true)
                ->getProcess();

            $process->run(
                function ($type, $data) use ($output) {
                    $output->write($data);
                }
            );
            $ret = $process->getExitCode();
        } else {
            $this->getApplication()->setAutoExit(false);
            $ret = $this->getApplication()->run(new ArrayInput($params), $output);
        }

        if (0 !== $ret) {
            $output->writeln(sprintf('<error>The command terminated with an error status (%s)</error>', $ret));
        }

        return $this;
    }
}

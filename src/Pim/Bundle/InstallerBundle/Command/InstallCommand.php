<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Doctrine\ORM\Mapping\ClassMetadata;

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
    /**
     * @staticvar string
     */
    const APP_NAME = 'Akeneo PIM';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('pim:install');
    }

    /**
     * {@inheritdoc}
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        parent::launchCommands($input, $output);

        $this
            ->runCommand(
                'pim:search:reindex',
                $input,
                $output,
                array('locale' => $this->getContainer()->getParameter('locale'))
            )
            ->runCommand('pim:versioning:refresh', $input, $output)
            ->runCommand(
                'pim:completeness:calculate',
                $input,
                $output
            );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('env') === 'behat') {
            $input->setOption('fixtures', self::LOAD_ORO);
        }

        return parent::loadFixtures($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Akeneo PIM requirements check:</info>');
        if (!class_exists('PimRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR
                . 'PimRequirements.php';
        }

        $collection = new \PimRequirements($this->getDirectoriesToCheck());

        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($collection->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable($collection->getPimRequirements(), 'Pim specific requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Optional recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them.'
            );
        }

        $output->writeln('');

        return $this;
    }

    /**
     * Get list of directories to check for PimRequirements
     *
     * @return array
     */
    protected function getDirectoriesToCheck()
    {
        $directories = array();
        $directories[] = $this->getContainer()->getParameter('upload_dir');
        $directories[] = $this->getContainer()->getParameter('archive_dir');

        return $directories;
    }
}

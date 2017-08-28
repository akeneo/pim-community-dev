<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Pim\Bundle\InstallerBundle\PimDirectoriesRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check requirements command
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckRequirementsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:check-requirements')
            ->setDescription('Check requirements for Akeneo PIM');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Akeneo PIM requirements check:</info>');

        $this->renderRequirements($input, $output, $this->getRequirements());
    }

    /**
     * Get Akeneo PIM requirements
     *
     * @return \PimRequirements
     */
    protected function getRequirements()
    {
        if (!class_exists('PimRequirements')) {
            $path = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'PimRequirements.php';
            require_once $path;
        }

        $directories = [];
        if ($this->getContainer()->getParameter('kernel.environment') !== 'behat') {
            $directories = $this->getDirectoriesContainer()->getDirectories();
        }

        $installStatusManager = $this->getContainer()->get('pim_installer.install_status_manager');
        array_push($directories, $installStatusManager->getAbsoluteDirectoryPath());

        return new \PimRequirements($directories);
    }

    /**
     * Render Akeneo PIM requirements
     *
     * @param InputInterface         $input
     * @param OutputInterface        $output
     * @param \RequirementCollection $collection
     *
     * @throws \RuntimeException
     */
    protected function renderRequirements(
        InputInterface $input,
        OutputInterface $output,
        \RequirementCollection $collection
    ) {
        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP requirements', $output);
        $this->renderTable($collection->getPimRequirements(), 'Pim requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            $this->renderTable($collection->getFailedRequirements(), 'Errors', $output);

            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them'
            );
        }
    }

    /**
     * Render requirements table
     *
     * @param array           $collection
     * @param string          $header
     * @param OutputInterface $output
     */
    protected function renderTable(array $collection, $header, OutputInterface $output)
    {
        $table = new Table($output);

        $table
            ->setHeaders(['Check  ', $header])
            ->setRows([]);

        foreach ($collection as $requirement) {
            if ($requirement->isFulfilled()) {
                $table->addRow(['OK', $requirement->getTestMessage()]);
            } else {
                $table->addRow(
                    [
                        $requirement->isOptional() ? 'WARNING' : 'ERROR',
                        $requirement->getHelpText()
                    ]
                );
            }
        }

        $table->render();
    }

    /**
     * @return PimDirectoriesRegistry
     */
    protected function getDirectoriesContainer()
    {
        return $this->getContainer()->get('pim_installer.directories_registry');
    }
}

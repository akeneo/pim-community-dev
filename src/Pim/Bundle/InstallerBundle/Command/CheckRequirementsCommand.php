<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR .'PimRequirements.php';
        }

        $directories = array();
        if ($this->getContainer()->getParameter('kernel.environment') !== 'behat') {
            $directories[] = $this->getContainer()->getParameter('upload_dir');
            $directories[] = $this->getContainer()->getParameter('archive_dir');
        }

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
        $this->renderTable($collection->getPhpIniRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getOroRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPimRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Mandatory requirements', $output);

        if (count($collection->getFailedRequirements())) {
            $this->renderTable($collection->getFailedRequirements(), 'Errors', $output);

            $output->writeln(
                '<error>Some system requirements are not fulfilled. Please check output messages and fix them</error>'
            );
            exit(0);
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
        $table = $this->getHelperSet()->get('table');

        $table
            ->setHeaders(array('Check  ', $header))
            ->setRows(array());

        foreach ($collection as $requirement) {
            if ($requirement->isFulfilled()) {
                $table->addRow(array('OK', $requirement->getTestMessage()));
            } else {
                $table->addRow(
                    array(
                        $requirement->isOptional() ? 'WARNING' : 'ERROR',
                        $requirement->getHelpText()
                    )
                );
            }
        }

        $table->render($output);
    }
}

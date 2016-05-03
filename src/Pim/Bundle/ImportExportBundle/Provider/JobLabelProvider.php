<?php

namespace Pim\Bundle\ImportExportBundle\Provider;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Model\Warning;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides UI translated label for background Jobs, Steps & StepElements
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobLabelProvider
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param TranslatorInterface $translator
     * @param ContainerInterface  $container
     */
    public function __construct(TranslatorInterface $translator, ContainerInterface $container)
    {
        $this->translator = $translator;
        $this->container = $container;
    }

    /**
     * Get the Job label with the given $jobExecution.
     * Example: "csv_product_import.label"
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    public function getJobLabel(JobExecution $jobExecution)
    {
        $job = $this->getJob($jobExecution->getJobInstance());
        $id = sprintf('%s.label', $job->getName());

        return $this->translator->trans($id);
    }

    /**
     * Get the Step label with the given $stepExecution, base on the Job name.
     * Example: "csv_product_import.perform.label"
     *
     * @param StepExecution $stepExecution
     *
     * @return string
     */
    public function getStepLabel(StepExecution $stepExecution)
    {
        $job = $this->getJob($stepExecution->getJobExecution()->getJobInstance());
        $id = sprintf('%s.%s.label', $job->getName(), $stepExecution->getStepName());

        return $this->translator->trans($id);
    }

    /**
     * Get the Step label with the given $stepWarning, base on the Job and Step name.
     * Example: "csv_product_import.perform.warning.duplicated.label"
     *
     * @param Warning $stepWarning
     *
     * @return string
     */
    public function getStepWarningLabel(Warning $stepWarning)
    {
        $job = $this->getJob($stepWarning->getStepExecution()->getJobExecution()->getJobInstance());
        $id = sprintf(
            '%s.%s.warning.%s.label',
            $job->getName(),
            $stepWarning->getStepExecution()->getStepName(),
            $stepWarning->getName()
        );

        return $this->translator->trans($id);
    }

    /**
     * @param JobInstance $jobInstance
     *
     * @return JobInterface
     */
    protected function getJob(JobInstance $jobInstance)
    {
        return $this->getConnectorRegistry()->getJob($jobInstance);
    }

    /**
     * @return ConnectorRegistry
     */
    protected function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}

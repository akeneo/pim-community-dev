<?php

namespace Oro\Bundle\ImportExportBundle\Job;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;

class JobExecutor
{
    const CONNECTOR_NAME = 'oro_importexport';

    const JOB_EXPORT_TO_CSV = 'entity_export_to_csv';
    const JOB_IMPORT_FROM_CSV = 'entity_import_from_csv';
    const JOB_VALIDATE_IMPORT_FROM_CSV = 'entity_import_validation_from_csv';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ConnectorRegistry
     */
    protected $jobRegistry;

    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    public function __construct(
        EntityManager $entityManager,
        ConnectorRegistry $jobRegistry,
        ContextRegistry $contextRegistry
    ) {
        $this->entityManager = $entityManager;
        $this->jobRegistry = $jobRegistry;
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * @param string $jobType
     * @param string $jobName
     * @param array $configuration
     * @return JobResult
     */
    public function executeJob($jobType, $jobName, array $configuration = array())
    {
        $jobResult = new JobResult();
        $jobResult->setSuccessful(false);

        $this->entityManager->beginTransaction();
        try {
            $jobInstance = new JobInstance(self::CONNECTOR_NAME, $jobType, $jobName);
            $jobInstance->setCode(uniqid($jobType . $jobName, true));
            $jobInstance->setLabel(sprintf('%s.%s', $jobType, $jobName));
            $jobInstance->setRawConfiguration($configuration);
            $this->entityManager->persist($jobInstance);

            $job = $this->jobRegistry->getJob($jobInstance);
            if (!$job) {
                throw new RuntimeException(sprintf('Can\'t find job "%s"', $jobName));
            }

            $jobExecution = new JobExecution();
            $jobExecution->setJobInstance($jobInstance);
            $this->entityManager->persist($jobExecution);

            $job->execute($jobExecution);

            $this->entityManager->flush();

            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $context = $this->contextRegistry->getByStepExecution($stepExecution);
                $jobResult->addContext($context);
            }

            if ($jobExecution->getStatus()->getValue() == BatchStatus::COMPLETED) {
                $this->entityManager->commit();
                $jobResult->setSuccessful(true);
            } else {
                $this->entityManager->rollback();
                foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                    $context = $this->contextRegistry->getByStepExecution($stepExecution);
                    foreach ($context->getErrors() as $error) {
                        $jobResult->addError($error);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $jobResult->addError($e->getMessage());
        }

        return $jobResult;
    }
}

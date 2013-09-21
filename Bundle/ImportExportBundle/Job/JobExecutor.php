<?php

namespace Oro\Bundle\ImportExportBundle\Job;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

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
        // create and persist job instance and job execution
        $jobInstance = new JobInstance(self::CONNECTOR_NAME, $jobType, $jobName);
        $jobInstance->setCode(uniqid($jobType . $jobName, true));
        $jobInstance->setLabel(sprintf('%s.%s', $jobType, $jobName));
        $jobInstance->setRawConfiguration($configuration);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);

        $this->entityManager->persist($jobInstance);

        $jobResult = new JobResult();
        $jobResult->setSuccessful(false);

        $this->entityManager->beginTransaction();
        try {
            $job = $this->jobRegistry->getJob($jobInstance);
            if (!$job) {
                throw new RuntimeException(sprintf('Can\'t find job "%s"', $jobName));
            }

            // TODO: Refactor whole logic of job execution to perform actions in transactions
            $job->execute($jobExecution);

            $stepExecutions = $jobExecution->getStepExecutions();
            $context = $this->contextRegistry->getByStepExecution($stepExecutions->first());
            $jobResult->setContext($context);

            $errors = $this->collectErrors($jobExecution);

            if ($jobExecution->getStatus()->getValue() == BatchStatus::COMPLETED && !$errors) {
                $this->entityManager->commit();
                $jobResult->setSuccessful(true);
            } else {
                $this->entityManager->rollback();
                foreach ($errors as $error) {
                    $jobResult->addError($error);
                }
                $jobInstance = $this->cloneJobInstance($jobInstance); // to save result to DB in any case
            }
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            $jobExecution->addFailureException($exception);
            $jobResult->addError($exception->getMessage());
            $jobInstance = $this->cloneJobInstance($jobInstance); // to save result to DB in any case
        }

        $this->entityManager->flush($jobInstance);

        $jobResult->setJobId($jobInstance->getId());

        return $jobResult;
    }

    /**
     * @param int $jobId
     * @return array
     * @throws LogicException
     */
    public function getJobErrors($jobId)
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->entityManager->find('OroBatchBundle:JobInstance', $jobId);
        if (!$jobId) {
            throw new LogicException(sprintf('Can\'t find job instance with ID %s', $jobId));
        }

        /** @var JobExecution $jobExecution */
        $jobExecution = $jobInstance->getJobExecutions()->first();
        if (!$jobExecution) {
            throw new LogicException(sprintf('No job execution found for job instance with ID %s', $jobId));
        }

        return $this->collectErrors($jobExecution);
    }

    /**
     * Deep clone of JobInstance object
     *
     * @param JobInstance $jobInstance
     * @return JobInstance
     */
    protected function cloneJobInstance(JobInstance $jobInstance)
    {
        $clonedJobInstance = clone $jobInstance;
        $clonedJobInstance->getJobExecutions()->clear();

        foreach ($jobInstance->getJobExecutions() as $jobExecution) {
            $clonedJobExecution = clone $jobExecution;
            $clonedJobExecution->getStepExecutions()->clear();

            $clonedJobExecution->setJobInstance($clonedJobInstance);
            $clonedJobInstance->addJobExecution($clonedJobExecution);

            foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                $clonedStepExecution = clone $stepExecution;
                $clonedStepExecution->setJobExecution($clonedJobExecution);
                $clonedJobExecution->addStepExecution($clonedStepExecution);
            }
        }

        $this->entityManager->remove($jobInstance);
        $this->entityManager->persist($clonedJobInstance);

        return $clonedJobInstance;
    }

    /**
     * @param JobExecution $jobExecution
     * @return array
     */
    protected function collectErrors(JobExecution $jobExecution)
    {
        $errors = array();
        foreach ($jobExecution->getAllFailureExceptions() as $exceptionData) {
            if (!empty($exceptionData['message'])) {
                $errors[] = $exceptionData['message'];
            }
        }

        return $errors;
    }
}

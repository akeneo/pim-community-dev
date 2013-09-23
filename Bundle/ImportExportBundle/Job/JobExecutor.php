<?php

namespace Oro\Bundle\ImportExportBundle\Job;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

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
        $jobInstance->setCode($this->generateJobCode($jobName));
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

            $errors = $this->collectErrors($jobExecution);

            if ($jobExecution->getStatus()->getValue() == BatchStatus::COMPLETED && !$errors) {
                $this->entityManager->commit();
                $jobResult->setSuccessful(true);
            } else {
                $this->entityManager->rollback();
                foreach ($errors as $error) {
                    $jobResult->addError($error);
                }
            }
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            $jobExecution->addFailureException($exception);
            $jobResult->addError($exception->getMessage());
        }

        // update job execution
        $this->entityManager->detach($jobInstance);
        $jobInstance = $this->updateJobInstance($jobInstance);
        $this->entityManager->persist($jobInstance);
        $this->entityManager->flush($jobInstance);

        // set data to JobResult
        $jobResult->setJobId($jobInstance->getId());
        $jobResult->setJobCode($jobInstance->getCode());
        /** @var JobExecution $jobExecution */
        $jobExecution = $jobInstance->getJobExecutions()->first();
        if ($jobExecution) {
            $stepExecution = $jobExecution->getStepExecutions()->first();
            if ($stepExecution) {
                $context = $this->contextRegistry->getByStepExecution($stepExecution);
                $jobResult->setContext($context);
            }
        }

        return $jobResult;
    }

    /**
     * @param string $jobCode
     * @return array
     * @throws LogicException
     */
    public function getJobErrors($jobCode)
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->getJobInstanceRepository()->findOneBy(array('code' => $jobCode));
        if (!$jobInstance) {
            throw new LogicException(sprintf('No job instance found with code %s', $jobCode));
        }

        /** @var JobExecution $jobExecution */
        $jobExecution = $jobInstance->getJobExecutions()->first();
        if (!$jobExecution) {
            throw new LogicException(sprintf('No job execution found for job instance with code %s', $jobCode));
        }

        return $this->collectErrors($jobExecution);
    }

    /**
     * @return EntityRepository
     */
    protected function getJobInstanceRepository()
    {
        return $this->entityManager->getRepository('OroBatchBundle:JobInstance');
    }

    /**
     * @param JobInstance $jobInstance
     * @return JobInstance
     */
    protected function updateJobInstance(JobInstance $jobInstance)
    {
        /** @var JobInstance $persistedJobInstance */
        $persistedJobInstance = $this->getJobInstanceRepository()->find($jobInstance->getId());
        if ($persistedJobInstance) {
            $jobExecutions = $jobInstance->getJobExecutions()->getValues();
            $persistedJobInstance->getJobExecutions()->clear();

            foreach ($jobExecutions as $jobExecution) {
                $clonedJobExecution = $this->cloneJobExecution($jobExecution);
                $clonedJobExecution->setJobInstance($persistedJobInstance);
                $persistedJobInstance->addJobExecution($clonedJobExecution);
            }

            $jobInstance = $persistedJobInstance;
        } else {
            $jobInstance = $this->cloneJobInstance($jobInstance);
        }

        return $jobInstance;
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
            $clonedJobExecution = $this->cloneJobExecution($jobExecution);
            $clonedJobExecution->setJobInstance($clonedJobInstance);
            $clonedJobInstance->addJobExecution($clonedJobExecution);
        }

        return $clonedJobInstance;
    }

    /**
     * @param JobExecution $jobExecution
     * @return JobExecution
     */
    protected function cloneJobExecution(JobExecution $jobExecution)
    {
        $clonedJobExecution = clone $jobExecution;
        $clonedJobExecution->getStepExecutions()->clear();

        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            $clonedStepExecution = clone $stepExecution;
            $clonedStepExecution->setJobExecution($clonedJobExecution);
            $clonedJobExecution->addStepExecution($clonedStepExecution);
        }

        return $clonedJobExecution;
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

    /**
     * @param string $prefix
     * @return string
     */
    protected function generateJobCode($prefix = '')
    {
        if ($prefix) {
            $prefix .= '_';
        }

        $prefix .= date('Y_m_d_H_i_s') . '_';

        return preg_replace('~\W~', '_', uniqid($prefix, true));
    }
}

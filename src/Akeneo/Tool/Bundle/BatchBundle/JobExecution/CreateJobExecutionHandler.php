<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\JobExecution;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create and persist a new JobExecution for the provided job code
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class CreateJobExecutionHandler implements CreateJobExecutionHandlerInterface
{
    public function __construct(
        private JobRepositoryInterface $jobRepository,
        private ManagerRegistry $doctrine,
        private JobRegistry $jobRegistry,
        private JobParametersFactory $jobParametersFactory,
        private JobParametersValidator $jobParametersValidator,
        private ValidatorInterface $validator,
    ) {
    }

    public function createFromBatchCode(
        string $batchCode,
        array $jobExecutionConfig,
        ?string $username
    ): JobExecution {
        $jobInstance = $this->getJobManager()->getRepository(JobInstance::class)
            ->findOneBy(['code' => $batchCode]);

        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $batchCode));
        }

        return $this->createFromJobInstance($jobInstance, $jobExecutionConfig, $username);
    }

    public function createFromJobInstance(
        JobInstance $jobInstance,
        array $jobExecutionConfig,
        ?string $username,
    ): JobExecution {
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParameters = $this->createJobParameters($job, $jobInstance, $jobExecutionConfig);
        $this->validateJob($job, $jobInstance, $jobParameters, $jobInstance->getCode());
        $jobExecution = $job->getJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        if (null !== $username) {
            $jobExecution->setUser($username);
            $job->getJobRepository()->updateJobExecution($jobExecution);
        }

        return $jobExecution;
    }

    private function getJobManager(): EntityManagerInterface
    {
        return $this->jobRepository->getJobManager();
    }

    private function getDefaultEntityManager(): ObjectManager
    {
        return $this->doctrine->getManager();
    }

    private function createJobParameters(
        JobInterface $job,
        JobInstance $jobInstance,
        array $jobExecutionConfig
    ): JobParameters {
        $rawParameters = array_merge($jobInstance->getRawParameters(), $jobExecutionConfig);

        return $this->jobParametersFactory->create($job, $rawParameters);
    }

    private function validateJob(JobInterface $job, JobInstance $jobInstance, JobParameters $jobParameters, string $code): void
    {
        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $this->getDefaultEntityManager()->merge($jobInstance);

        $jobInstanceViolations = $this->validator->validate($jobInstance, new JobInstanceConstraint());

        if (0 < $jobInstanceViolations->count()) {
            throw new InvalidJobException($code, $job->getName(), $jobInstanceViolations);
        }

        $jobParametersViolations = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if (0 < $jobParametersViolations->count()) {
            throw new InvalidJobException($code, $job->getName(), $jobParametersViolations);
        }

        $this->getDefaultEntityManager()->clear(get_class($jobInstance));
    }
}

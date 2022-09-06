<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\JobExecution;

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
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Create and persist a new JobExecution for the provided job code
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
class CreateJobExecutionHandler
{
    public function __construct(
        private JobRepositoryInterface $jobRepository,
        private ManagerRegistry $doctrine,
        private JobRegistry $jobRegistry,
        private JobParametersFactory $jobParametersFactory,
        private JobParametersValidator $jobParametersValidator,
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

        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParameters = $this->createJobParameters($job, $jobInstance, $jobExecutionConfig);
        $this->validateJobParameters($job, $jobInstance, $jobParameters, $batchCode);
        $jobExecution = $job->getJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        if (null !== $username) {
            $jobExecution->setUser($username);
            $job->getJobRepository()->updateJobExecution($jobExecution);
        }

        return $jobExecution;
    }

    protected function getJobManager(): EntityManagerInterface
    {
        return $this->jobRepository->getJobManager();
    }

    protected function getDefaultEntityManager(): ObjectManager
    {
        return $this->doctrine->getManager();
    }

    protected function createJobParameters(
        JobInterface $job,
        JobInstance $jobInstance,
        array $jobExecutionConfig
    ): JobParameters {
        $rawParameters = array_merge($jobInstance->getRawParameters(), $jobExecutionConfig);

        return $this->jobParametersFactory->create($job, $rawParameters);
    }

    /**
     * @throws \RuntimeException
     */
    protected function validateJobParameters(
        JobInterface $job,
        JobInstance $jobInstance,
        JobParameters $jobParameters,
        string $code
    ): void {
        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $this->getDefaultEntityManager()->merge($jobInstance);
        $errors = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if (\count($errors) > 0) {
            throw new \RuntimeException(
                sprintf(
                    'Job instance "%s" running the job "%s" with parameters "%s" is invalid because of "%s"',
                    $code,
                    $job->getName(),
                    print_r($jobParameters->all(), true),
                    $this->getErrorMessages($errors)
                )
            );
        }
    }

    private function getErrorMessages(ConstraintViolationList $errors): string
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }
}

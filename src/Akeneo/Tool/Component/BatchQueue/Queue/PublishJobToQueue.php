<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Push a registered job instance to execute into the job execution queue.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishJobToQueue
{
    public function __construct(
        private string $kernelEnv,
        private DoctrineJobRepository $jobRepository,
        private ValidatorInterface $validator,
        private JobRegistry $jobRegistry,
        private JobParametersFactory $jobParametersFactory,
        private EntityManagerInterface $entityManager,
        private JobParametersValidator $jobParametersValidator,
        private JobExecutionQueueInterface $jobExecutionQueue,
        private JobExecutionMessageFactory $jobExecutionMessageFactory,
        private EventDispatcherInterface $eventDispatcher,
        private BatchLogHandler $batchLogHandler
    ) {
    }

    public function publish(
        string $jobInstanceCode,
        array $config,
        bool $noLog = false,
        ?string $username = null,
        ?string $email = null,
    ): void {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobRepository
            ->getJobManager()
            ->getRepository(JobInstance::class)
            ->findOneBy(['code' => $jobInstanceCode]);

        if (null === $jobInstance) {
            throw new \InvalidArgumentException(sprintf('Could not find job instance "%s".', $jobInstanceCode));
        }

        $options = ['env' => $this->kernelEnv];

        if (null !== $email) {
            $errors = $this->validator->validate($email, new Assert\Email());
            if (count($errors) > 0) {
                throw new \RuntimeException(
                    sprintf('Email "%s" is invalid: %s', $email, $this->getErrorMessages($errors))
                );
            }
            $options['email'] = $email;
        }

        if (true === $noLog) {
            $options['no-log'] = true;
        }

        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParameters = $this->createJobParameters($job, $jobInstance, $config);

        $this->validateJob($job, $jobInstance, $jobParameters, $jobInstanceCode);

        $jobExecution = $this->jobRepository->createJobExecution($job, $jobInstance, $jobParameters);

        $this->batchLogHandler->setSubDirectory((string)$jobExecution->getId());

        if (null !== $username) {
            $jobExecution->setUser($username);
        }

        $this->jobRepository->updateJobExecution($jobExecution);

        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromJobInstance(
            $jobInstance,
            $jobExecution->getId(),
            $options
        );
        $this->jobExecutionQueue->publish($jobExecutionMessage);

        $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_CREATED, $jobExecution);
    }

    private function createJobParameters(JobInterface $job, JobInstance $jobInstance, array $config): JobParameters
    {
        $rawParameters = $jobInstance->getRawParameters();

        $rawParameters = array_merge($rawParameters, $config);

        return $this->jobParametersFactory->create($job, $rawParameters);
    }

    private function validateJob(JobInterface $job, JobInstance $jobInstance, JobParameters $jobParameters, string $code): void
    {
        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $this->entityManager->merge($jobInstance);

        $jobInstanceViolations = $this->validator->validate($jobInstance, new JobInstanceConstraint());

        if (0 < $jobInstanceViolations->count()) {
            throw new InvalidJobException($code, $job->getName(), $jobInstanceViolations);
        }

        $jobParametersViolations = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if (0 < $jobParametersViolations->count()) {
            throw new InvalidJobException($code, $job->getName(), $jobParametersViolations);
        }

        $this->entityManager->clear(get_class($jobInstance));
    }

    private function getErrorMessages(ConstraintViolationListInterface $errors): string
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }

    private function dispatchJobExecutionEvent(string $eventName, JobExecution $jobExecution): void
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
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
    /** @var string */
    private $jobInstanceClass;

    /** @var DoctrineJobRepository */
    private $jobRepository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var string */
    private $kernelEnv;

    /** @var JobRegistry */
    private $jobRegistry;

    /** @var JobParametersFactory */
    private $jobParametersFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var JobParametersValidator */
    private $jobParametersValidator;

    /** @var JobExecutionQueueInterface */
    private $jobExecutionQueue;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        string $jobInstanceClass,
        string $kernelEnv,
        DoctrineJobRepository $jobRepository,
        ValidatorInterface $validator,
        JobRegistry $jobRegistry,
        JobParametersFactory $jobParametersFactory,
        EntityManagerInterface $entityManager,
        JobParametersValidator $jobParametersValidator,
        JobExecutionQueueInterface $jobExecutionQueue,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->jobInstanceClass = $jobInstanceClass;
        $this->jobRepository = $jobRepository;
        $this->validator = $validator;
        $this->kernelEnv = $kernelEnv;
        $this->jobRegistry = $jobRegistry;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->entityManager = $entityManager;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->jobExecutionQueue = $jobExecutionQueue;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function publish(
        string $jobInstanceCode,
        array $config,
        bool $noLog = false,
        ?string $username = null,
        ?string $email = null
    ): void {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobRepository
            ->getJobManager()
            ->getRepository($this->jobInstanceClass)
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

        $jobParameters = $this->createJobParameters($jobInstance, $config);
        $this->validateJobParameters($jobInstance, $jobParameters, $jobInstanceCode);
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, $jobParameters);

        if (null !== $username) {
            $jobExecution->setUser($username);
            $this->jobRepository->updateJobExecution($jobExecution);
        }

        $this->jobRepository->updateJobExecution($jobExecution);

        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), $options);

        $this->jobExecutionQueue->publish($jobExecutionMessage);

        $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_CREATED, $jobExecution);
    }

    private function createJobParameters(JobInstance $jobInstance, array $config): JobParameters
    {
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $rawParameters = $jobInstance->getRawParameters();

        $rawParameters = array_merge($rawParameters, $config);
        $jobParameters = $this->jobParametersFactory->create($job, $rawParameters);

        return $jobParameters;
    }

    private function validateJobParameters(JobInstance $jobInstance, JobParameters $jobParameters, string $code) : void
    {
        // We merge the JobInstance from the JobManager EntityManager to the DefaultEntityManager
        // in order to be able to have a working UniqueEntity validation
        $this->entityManager->merge($jobInstance);
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $errors = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if (count($errors) > 0) {
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

    private function dispatchJobExecutionEvent($eventName, JobExecution $jobExecution): void
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->eventDispatcher->dispatch($eventName, $event);
    }
}

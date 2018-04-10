<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Publish job execution into a queue in order to be launched asynchronously.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueueJobLauncher implements JobLauncherInterface
{
    /** @var JobRepositoryInterface */
    private $jobRepository;

    /** @var JobParametersFactory */
    private $jobParametersFactory;

    /** @var JobRegistry */
    private $jobRegistry;

    /** @var JobParametersValidator */
    private $jobParametersValidator;

    /** @var JobExecutionQueueInterface */
    private $queue;

    /** @var string */
    private $environment;

    /**
     * @param JobRepositoryInterface     $jobRepository
     * @param JobParametersFactory       $jobParametersFactory
     * @param JobRegistry                $jobRegistry
     * @param JobParametersValidator     $jobParametersValidator
     * @param JobExecutionQueueInterface $queue
     * @param string                     $environment
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobParametersValidator $jobParametersValidator,
        JobExecutionQueueInterface $queue,
        string $environment
    ) {
        $this->jobRepository = $jobRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobRegistry = $jobRegistry;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->queue = $queue;
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = []) : JobExecution
    {
        $options = ['env' => $this->environment];
        if (isset($configuration['send_email']) && method_exists($user, 'getEmail')) {
            $options['email'] = $user->getEmail();
            unset($configuration['send_email']);
        }

        $jobExecution = $this->createJobExecution($jobInstance, $user, $configuration);
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), $options);

        $this->queue->publish($jobExecutionMessage);

        return $jobExecution;
    }

    /**
     * Create a jobExecution
     *
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     * @param array         $configuration
     *
     * @throws \RuntimeException
     *
     * @return JobExecution
     */
    private function createJobExecution(JobInstance $jobInstance, UserInterface $user, array $configuration) : JobExecution
    {
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);

        $jobParameters = $this->jobParametersFactory->create($job, $configuration);

        $errors = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if ($errors->count() > 0) {
            throw new \RuntimeException(
                sprintf(
                    'Job instance "%s" running the job "%s" with parameters "%s" is invalid because of "%s"',
                    $jobInstance->getCode(),
                    $job->getName(),
                    print_r($jobParameters->all(), true),
                    $this->getErrorMessages($errors)
                )
            );
        }

        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setUser($user->getUsername());
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return string
     */
    private function getErrorMessages(ConstraintViolationListInterface $errors): string
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf('%s  - %s', PHP_EOL, $error);
        }

        return $errorsStr;
    }
}

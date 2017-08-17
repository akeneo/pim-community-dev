<?php

namespace Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobExecutionMessage;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Queue\JobExecutionQueueInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *  Launch a job by putting it in the queue in order to be processed asynchronously.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class SimpleJobLauncher implements JobLauncherInterface
{
    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var JobExecutionQueueInterface */
    protected $jobExecutionQueue;

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $logDir;

    /**
     * Constructor
     *
     * @param JobRepositoryInterface     $jobRepository
     * @param JobParametersFactory       $jobParametersFactory
     * @param JobRegistry                $jobRegistry
     * @param JobExecutionQueueInterface $jobExecutionQueue
     * @param string                     $rootDir
     * @param string                     $environment
     * @param string                     $logDir
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobExecutionQueueInterface $jobExecutionQueue,
        $rootDir,
        $environment,
        $logDir
    ) {
        $this->jobRepository = $jobRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobRegistry = $jobRegistry;
        $this->jobExecutionQueue = $jobExecutionQueue;
        $this->rootDir = $rootDir;
        $this->environment = $environment;
        $this->logDir = $logDir;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = [])
    {
        $options = [
            'env' => $this->environment,
        ];

        if (isset($configuration['send_email']) && method_exists($user, 'getEmail')) {
            $options['email'] = $user->getEmail();
            unset($configuration['send_email']);
        }

        $jobExecution = $this->createJobExecution($jobInstance, $user, $configuration);

        $jobExecutionMessage = new JobExecutionMessage(
            $jobExecution,
            'akeneo:batch:job',
            $options
        );

        $this->jobExecutionQueue->publish($jobExecutionMessage);

        return $jobExecution;
    }

    /**
     * Create a jobExecution
     *
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     * @param array         $configuration
     *
     * @return JobExecution
     */
    protected function createJobExecution(JobInstance $jobInstance, UserInterface $user, array $configuration)
    {
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);
        $jobParameters = $this->jobParametersFactory->create($job, $configuration);
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setUser($user->getUsername());
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }
}

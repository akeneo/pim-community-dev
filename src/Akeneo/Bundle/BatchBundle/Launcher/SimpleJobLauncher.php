<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
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

    /** @var JobParametersValidator */
    protected $jobParametersValidator;

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $logDir;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param JobRepositoryInterface    $jobRepository
     * @param JobParametersFactory      $jobParametersFactory
     * @param JobRegistry               $jobRegistry
     * @param JobParametersValidator    $jobParametersValidator
     * @param string                    $rootDir
     * @param string                    $environment
     * @param string                    $logDir
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobParametersValidator $jobParametersValidator,
        $rootDir,
        $environment,
        $logDir,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->jobRepository = $jobRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobRegistry = $jobRegistry;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->rootDir = $rootDir;
        $this->environment = $environment;
        $this->logDir = $logDir;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = []) : JobExecution
    {
        $emailParameter = '';
        if (isset($configuration['send_email']) && method_exists($user, 'getEmail')) {
            $emailParameter = sprintf('--email=%s', escapeshellarg($user->getEmail()));
            unset($configuration['send_email']);
        }

        $jobExecution = $this->createJobExecution($jobInstance, $user, $configuration);
        $pathFinder = new PhpExecutableFinder();

        $cmd = sprintf(
            '%s %s%sconsole akeneo:batch:job --env=%s %s %s %s >> %s%sbatch_execute.log 2>&1',
            $pathFinder->find(),
            sprintf('%s%s..%sbin', $this->rootDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $this->environment,
            $emailParameter,
            escapeshellarg($jobInstance->getCode()),
            $jobExecution->getId(),
            $this->logDir,
            DIRECTORY_SEPARATOR
        );

        $this->launchInBackground($cmd);

        return $jobExecution;
    }

    /**
     * Launch command in background
     *
     * Please note we do not use Symfony Process as it has some problem
     * when executed from HTTP request that stop fast (race condition that makes
     * the process cloning fail when the parent process, i.e. HTTP request, stops
     * at the same time)
     *
     * @param string $cmd
     */
    protected function launchInBackground(string $cmd) : void
    {
        exec($cmd . ' &');
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
    protected function createJobExecution(JobInstance $jobInstance, UserInterface $user, array $configuration) : JobExecution
    {
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);

        $jobParameters = $this->jobParametersFactory->create($job, $configuration);

        $errors = $this->jobParametersValidator->validate($job, $jobParameters, ['Default', 'Execution']);

        if (count($errors) > 0) {
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

        $this->dispatchJobExecutionEvent(EventInterface::JOB_EXECUTION_CREATED, $jobExecution);

        return $jobExecution;
    }

    /**
     * Trigger event linked to JobExecution
     *
     * @param string       $eventName    Name of the event
     * @param JobExecution $jobExecution Object to store job execution
     */
    private function dispatchJobExecutionEvent($eventName, JobExecution $jobExecution)
    {
        $event = new JobExecutionEvent($jobExecution);
        $this->eventDispatcher->dispatch($eventName, $event);
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

<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\User\UserInterface;

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

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var string */
    protected $logDir;

    /**
     * Constructor
     *
     * @param JobRepositoryInterface $jobRepository
     * @param JobParametersFactory   $jobParametersFactory
     * @param JobRegistry            $jobRegistry
     * @param string                 $rootDir
     * @param string                 $environment
     * @param string                 $logDir
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        $rootDir,
        $environment,
        $logDir
    ) {
        $this->jobRepository = $jobRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobRegistry = $jobRegistry;
        $this->rootDir = $rootDir;
        $this->environment = $environment;
        $this->logDir = $logDir;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = []) : JobExecution
    {
        $jobExecution = $this->createJobExecution($jobInstance, $user);
        $executionId = $jobExecution->getId();
        $pathFinder = new PhpExecutableFinder();

        $emailParameter = '';
        if (isset($configuration['send_email']) && method_exists($user, 'getEmail')) {
            $emailParameter = sprintf('--email=%s', escapeshellarg($user->getEmail()));
            unset($configuration['send_email']);
        }

        $encodedConfiguration = json_encode($configuration, JSON_HEX_APOS);
        $cmd = sprintf(
            '%s %s%sconsole akeneo:batch:job --env=%s %s %s %s %s >> %s%sbatch_execute.log 2>&1',
            $pathFinder->find(),
            sprintf('%s%s..%sbin', $this->rootDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $this->environment,
            $emailParameter,
            escapeshellarg($jobInstance->getCode()),
            $executionId,
            !empty($configuration) ? sprintf('--config=%s', escapeshellarg($encodedConfiguration)) : '',
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
     *
     * @return JobExecution
     */
    protected function createJobExecution(JobInstance $jobInstance, UserInterface $user) : JobExecution
    {
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParameters = $this->jobParametersFactory->create($job, $jobInstance->getRawParameters());
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setUser($user->getUsername());
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }
}

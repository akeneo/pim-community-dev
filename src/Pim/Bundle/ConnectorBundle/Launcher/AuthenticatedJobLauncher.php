<?php

declare(strict_types=1);

namespace Pim\Bundle\ConnectorBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Job launcher authenticated with a username.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticatedJobLauncher implements JobLauncherInterface
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
        $emailParameter = '';
        if (isset($configuration['send_email']) && method_exists($user, 'getEmail')) {
            $emailParameter = sprintf('--email=%s', escapeshellarg($user->getEmail()));
            unset($configuration['send_email']);
        }

        $jobExecution = $this->createJobExecution($jobInstance, $user, $configuration);
        $executionId = $jobExecution->getId();
        $pathFinder = new PhpExecutableFinder();

        $cmd = sprintf(
            '%s %s%sconsole pim:batch:job --env=%s %s %s %s %s >> %s%sbatch_execute.log 2>&1',
            $pathFinder->find(),
            sprintf('%s%s..%sbin', $this->rootDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $this->environment,
            $emailParameter,
            escapeshellarg($jobInstance->getCode()),
            escapeshellarg($user->getUsername()),
            $executionId,
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
     * @return JobExecution
     */
    protected function createJobExecution(JobInstance $jobInstance, UserInterface $user, array $configuration) : JobExecution
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

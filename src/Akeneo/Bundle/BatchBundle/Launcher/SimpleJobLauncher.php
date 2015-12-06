<?php

namespace Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
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

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var array */
    protected $config = [];

    /**
     * Constructor
     *
     * @param JobRepositoryInterface   $jobRepository
     * @param string                   $rootDir
     * @param string                   $environment
     */
    public function __construct(JobRepositoryInterface $jobRepository, $rootDir, $environment)
    {
        $this->jobRepository = $jobRepository;
        $this->rootDir       = $rootDir;
        $this->environment   = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, $rawConfiguration = null)
    {
        $jobExecution = $this->createJobExecution($jobInstance, $user);
        $executionId  = $jobExecution->getId();
        $pathFinder   = new PhpExecutableFinder();

        //TODO we should not rely on such test
        $emailParameter = '';
        if ($this->isConfigTrue('email') && method_exists($user, 'getEmail')) {
            $emailParameter = sprintf('--email="%s"', $user->getEmail());
        }

        $cmd = sprintf(
            '%s %s/console akeneo:batch:job --env=%s %s %s %s %s >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $this->rootDir,
            $this->environment,
            $emailParameter,
            $jobInstance->getCode(),
            $executionId,
            !empty($rawConfiguration) ? sprintf('--config="%s"', $rawConfiguration) : '',
            $this->rootDir
        );

        $this->launchInBackground($cmd);

        return $jobExecution;
    }

    /**
     * {@inheridoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * {@inheridoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Is key true in configuration
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isConfigTrue($key)
    {
        return isset($this->config[$key]) && true === $this->config[$key];
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
    protected function launchInBackground($cmd)
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
    protected function createJobExecution(JobInstance $jobInstance, UserInterface $user)
    {
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance);
        $jobExecution->setUser($user->getUsername());
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }
}

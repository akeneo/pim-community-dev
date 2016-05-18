<?php

namespace Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /**
     * Constructor
     *
     * @param JobRepositoryInterface $jobRepository
     * @param JobParametersFactory   $jobParametersFactory
     * @param ContainerInterface     $container
     * @param string                 $rootDir
     * @param string                 $environment
     */
    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        ContainerInterface $container,
        $rootDir,
        $environment
    ) {
        $this->jobRepository        = $jobRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->container            = $container;
        $this->rootDir              = $rootDir;
        $this->environment          = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = [])
    {
        $jobExecution = $this->createJobExecution($jobInstance, $user);
        $executionId  = $jobExecution->getId();
        $pathFinder   = new PhpExecutableFinder();

        $emailParameter = '';
        if (isset($configuration['send_email']) && method_exists($user, 'getEmail')) {
            $emailParameter = sprintf('--email="%s"', $user->getEmail());
            unset($configuration['send_email']);
        }

        $encodedConfiguration = addslashes(json_encode($configuration));
        $cmd = sprintf(
            '%s %s/console akeneo:batch:job --env=%s %s %s %s %s >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $this->rootDir,
            $this->environment,
            $emailParameter,
            $jobInstance->getCode(),
            $executionId,
            !empty($configuration) ? sprintf('--config="%s"', $encodedConfiguration) : '',
            $this->rootDir
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
        $job = $this->getConnectorRegistry()->getJob($jobInstance);
        $jobParameters = $this->jobParametersFactory->create($job, $jobInstance->getRawConfiguration());
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, $jobParameters);
        $jobExecution->setUser($user->getUsername());
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    /**
     * Should be changed with TIP-418, here we work around a circular reference due to the way we instanciate the whole
     * Job classes in the DIC
     *
     * @return ConnectorRegistry
     */
    final protected function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}

<?php

namespace Pim\Bundle\ImportExportBundle\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Job manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobManager
{
    /**
     * @var SmartManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $jobExecutionClass;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param SmartManagerRegistry     $managerRegistry
     * @param string                   $jobExecutionClass
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        SmartManagerRegistry $managerRegistry,
        $jobExecutionClass
    ) {
        $this->eventDispatcher   = $eventDispatcher;
        $this->managerRegistry   = $managerRegistry;
        $this->jobExecutionClass = $jobExecutionClass;
    }

    /**
     * Launch a job with command
     *
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     * @param string        $rootDir
     * @param string        $environment
     * @param boolean       $uploadMode
     *
     * @return JobExecution
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, $rootDir, $environment, $uploadMode)
    {
        $jobExecution = $this->create($jobInstance, $user);
        $instanceCode = $jobExecution->getJobInstance()->getCode();
        $executionId  = $jobExecution->getId();
        $pathFinder  = new PhpExecutableFinder();

        $cmd = sprintf(
            '%s %s/console akeneo:batch:job --env=%s --email="%s" %s %s %s >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $rootDir,
            $environment,
            $user->getEmail(),
            $uploadMode ? sprintf('-c \'%s\'', json_encode($jobInstance->getJob()->getConfiguration())) : '',
            $instanceCode,
            $executionId,
            $rootDir
        );
        // Please note we do not use Symfony Process as it has some problem
        // when executed from HTTP request that stop fast (race condition that makes
        // the process cloning fail when the parent process, i.e. HTTP request, stops
        // at the same time)
        exec($cmd . ' &');

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EXECUTE, new GenericEvent($jobInstance));

        return $jobExecution;
    }

    /**
     * Instantiate a new job execution
     *
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     *
     * @return JobExecution
     * @throws \Exception
     */
    protected function create(JobInstance $jobInstance, UserInterface $user)
    {
        $jobExecution = new $this->jobExecutionClass();

        $jobExecution->setJobInstance($jobInstance)->setUser($user->getUsername());
        $manager = $this->managerRegistry->getManagerForClass(get_class($jobExecution));
        $manager->persist($jobExecution);
        $manager->flush($jobExecution);

        return $jobExecution;
    }
}

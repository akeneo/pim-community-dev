<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditJobManager
{
    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $jobExecutionClass;

    /**  @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $jobExecutionClass
     * @param string                   $rootDir
     * @param string                   $environment
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $jobExecutionClass,
        $rootDir,
        $environment
    ) {
        $this->rootDir             = $rootDir;
        $this->environment         = $environment;
        $this->eventDispatcher     = $eventDispatcher;
        $this->jobExecutionClass   = $jobExecutionClass;
        $this->objectManager       = $objectManager;
    }

    /**
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     * @param string        $rawConfiguration
     *
     * @return \Akeneo\Bundle\BatchBundle\Entity\JobExecution
     */
    public function launchJob(JobInstance $jobInstance, UserInterface $user, $rawConfiguration)
    {
        $jobExecution = $this->create($jobInstance, $user);
        $executionId  = $jobExecution->getId();
        $pathFinder   = new PhpExecutableFinder();

        $cmd = sprintf(
            '%s %s/console akeneo:batch:job --env=%s %s %s --config="%s" >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $this->rootDir,
            $this->environment,
            $jobInstance->getCode(),
            $executionId,
            $rawConfiguration,
            $this->rootDir
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
        $this->objectManager->persist($jobExecution);
        $this->objectManager->flush($jobExecution);

        return $jobExecution;
    }
}

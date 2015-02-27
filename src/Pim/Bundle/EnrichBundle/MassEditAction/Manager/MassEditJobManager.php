<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Manager;

use Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry;
use Pim\Bundle\ImportExportBundle\Manager\JobManager;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
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
class MassEditJobManager extends JobManager
{
    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var OperatorRegistry */
    protected $operatorRegistry;

    /** @var NotificationManager */
    protected $notificationManager;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param OperatorRegistry         $operatorRegistry
     * @param NotificationManager      $notificationManager
     * @param string                   $jobExecutionClass
     * @param string                   $rootDir
     * @param string                   $environment
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        OperatorRegistry $operatorRegistry,
        NotificationManager $notificationManager,
        $jobExecutionClass,
        $rootDir,
        $environment
    ) {
        parent::__construct($objectManager, $eventDispatcher, $jobExecutionClass);

        $this->operatorRegistry    = $operatorRegistry;
        $this->rootDir             = $rootDir;
        $this->environment         = $environment;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @param int $id
     *
     * @return JobInstance
     */
    public function getJobInstance($id)
    {
        $jobInstance = $this->objectManager->find('AkeneoBatchBundle:JobInstance', $id);

        return $jobInstance;
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
        $pathFinder  = new PhpExecutableFinder();

        $cmd = sprintf(
            '%s %s/console akeneo:batch:job --env=%s %s --config=\'[%s]\' >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $this->rootDir,
            $this->environment,
            $executionId,
            $rawConfiguration,
            $this->rootDir
        );

        // Please note we do not use Symfony Process as it has some problem
        // when executed from HTTP request that stop fast (race condition that makes
        // the process cloning fail when the parent process, i.e. HTTP request, stops
        // at the same time)
        exec($cmd . ' &');

        return $jobExecution;
    }
}

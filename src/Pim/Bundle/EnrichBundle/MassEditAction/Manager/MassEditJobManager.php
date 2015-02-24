<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Manager;

use Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry;
use Pim\Bundle\ImportExportBundle\Manager\JobManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
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
    private $operatorRegistry;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param OperatorRegistry         $operatorRegistry
     * @param string                   $jobExecutionClass
     * @param string                   $rootDir
     * @param string                   $environment
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        OperatorRegistry $operatorRegistry,
        $jobExecutionClass,
        $rootDir,
        $environment
    ) {
        parent::__construct($objectManager, $eventDispatcher, $jobExecutionClass);

        $this->operatorRegistry = $operatorRegistry;
        $this->rootDir = $rootDir;
        $this->environment = $environment;
    }

    /**
     * @param $id
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
     *
     * @return \Akeneo\Bundle\BatchBundle\Entity\JobExecution
     */
    public function launchJob(JobInstance $jobInstance, UserInterface $user)
    {
        $jobExecution = $this->create($jobInstance, $user);
        $instanceCode = $jobExecution->getJobInstance()->getCode();
        $executionId  = $jobExecution->getId();
        $pathFinder  = new PhpExecutableFinder();
        $operator = $this->getOperator($jobInstance);
        $jobRawConfig = $jobInstance->getRawConfiguration();
        $pimFilters = $jobRawConfig['filters'];

        $cmd = sprintf(
            '%s %s/console pim:mass-edit:%s --env=%s \'%s\' %s %s %s >> %s/logs/batch_execute.log 2>&1',
            $pathFinder->find(),
            $this->rootDir,
            $operator->getOperationAlias(),
            $this->environment,
            json_encode($pimFilters),
            (int) $operator->getOperation()->isToEnable(),
            $instanceCode,
            $executionId,
            $this->rootDir
        );

        // Please note we do not use Symfony Process as it has some problem
        // when executed from HTTP request that stop fast (race condition that makes
        // the process cloning fail when the parent process, i.e. HTTP request, stops
        // at the same time)
        exec($cmd . ' &');

//        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EXECUTE, new GenericEvent($jobInstance));

        return $jobExecution;
    }

    /**
     * @param JobInstance $jobInstance
     *
     * @return \Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionOperator
     */
    protected function getOperator(JobInstance $jobInstance)
    {
        $rawConfiguration = $jobInstance->getRawConfiguration();
        $operator = $this->operatorRegistry->getOperator($rawConfiguration['gridName']);

        $operator
            ->setOperationAlias($rawConfiguration['operationAlias'])
            ->initializeOperation();

        return $operator;
    }
}

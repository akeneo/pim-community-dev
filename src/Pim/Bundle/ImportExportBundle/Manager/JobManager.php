<?php

namespace Pim\Bundle\ImportExportBundle\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
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
class JobManager implements SaverInterface, RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $jobExecutionClass;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $jobExecutionClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $jobExecutionClass
    ) {
        $this->objectManager     = $objectManager;
        $this->eventDispatcher   = $eventDispatcher;
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
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof JobInstance) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Akeneo\Bundle\BatchBundle\Entity\JobInstance, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->persist($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof JobInstance) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Akeneo\Bundle\BatchBundle\Entity\JobInstance, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->remove($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Instantiate a new job execution
     *
     * @param JobInstance   $jobInstance
     * @param UserInterface $user
     *
     * @throws \Exception
     *
     * @return JobExecution
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

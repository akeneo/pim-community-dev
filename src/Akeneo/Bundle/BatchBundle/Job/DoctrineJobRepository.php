<?php

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;

/**
 * Class peristing JobExecution and StepExecution states.
 * This class instantiates a specific EntityManager to avoid
 * polluting the transactional state of data coming through the
 * batch.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.JobRepository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DoctrineJobRepository implements JobRepositoryInterface
{
    /* @var EntityManager */
    protected $jobManager = null;

    /* @var string */
    protected $jobExecutionClass;

    /**
     * Provides the doctrine entity manager
     *
     * @param EntityManager $entityManager
     * @param string        $jobExecutionClass
     */
    public function __construct(
        EntityManager $entityManager,
        $jobExecutionClass = 'Akeneo\\Component\\Batch\\Model\\JobExecution'
    ) {
        $currentConn = $entityManager->getConnection();

        $currentConnParams = $currentConn->getParams();
        if (isset($currentConnParams['pdo'])) {
            unset($currentConnParams['pdo']);
        }

        $jobConn = new Connection(
            $currentConnParams,
            $currentConn->getDriver(),
            $currentConn->getConfiguration()
        );

        $jobManager = EntityManager::create(
            $jobConn,
            $entityManager->getConfiguration()
        );

        $this->jobManager        = $jobManager;
        $this->jobExecutionClass = $jobExecutionClass;
    }

    /**
     * Get the specific Job entityManager
     *
     * @return EntityManager
     */
    public function getJobManager()
    {
        return $this->jobManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createJobExecution(JobInstance $jobInstance)
    {
        if (null !== $jobInstance->getId()) {
            $jobInstance = $this->jobManager->merge($jobInstance);
        } else {
            $this->jobManager->persist($jobInstance);
        }

        $jobExecution = new $this->jobExecutionClass();
        $jobExecution->setJobInstance($jobInstance);

        $this->updateJobExecution($jobExecution);

        return $jobExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function updateJobExecution(JobExecution $jobExecution)
    {
        $this->checkConnection();
        $this->jobManager->persist($jobExecution);
        $this->jobManager->flush($jobExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
        $this->checkConnection();
        $this->jobManager->persist($stepExecution);
        $this->jobManager->flush($stepExecution);
    }

    /**
     * Ping the Server, if not available then reset the connection.
     * @author Cristian Quiroz <cq@amp.co>
     */
    public function checkConnection()
    {
        $connection = $this->jobManager->getConnection();
        if ($this->pingConnection($connection) === false) {
            $connection->close();
            $connection->connect();
        }
    }

    /**
     * Pings the server, returns false if it's not available.
     * There is a ping() method in Doctrine\DBAL\Connection in the doctrine/dbal package
     * as of 2.5.0, but  we are currently on 2.4.x
     * @return bool
     * @author Cristian Quiroz <cq@amp.co>
     */
    private function pingConnection()
    {
        $connection = $this->jobManager->getConnection();
        $connection->connect();
        try {
            $connection->query($connection->getDatabasePlatform()->getDummySelectSQL());
            return true;
        } catch (DBALException $e) {
            return false;
        }
    }
}

<?php

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

/**
 * Class persisting JobExecution and StepExecution states.
 * This class instantiates a specific EntityManager to avoid
 * polluting the transactional state of data coming through the
 * batch.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.JobRepository
 *
 * TODO TIP-385: re-wite this implementation to avoid to open a dedicated connection like this
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DoctrineJobRepository implements JobRepositoryInterface
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /* @var EntityManager */
    protected $jobManager = null;

    /* @var string */
    protected $jobExecutionClass;

    /* @var int */
    protected $batchSize;

    /**
     * Provides the doctrine entity manager
     *
     * @param EntityManager $entityManager
     * @param string        $jobExecutionClass
     * @param string        $jobInstanceClass
     * @param string        $jobInstanceRepoClass
     */
    public function __construct(
        EntityManager $entityManager,
        $jobExecutionClass,
        $jobInstanceClass,
        $jobInstanceRepoClass,
        $batchSize = 100
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

        $this->jobManager = $jobManager;
        $this->jobExecutionClass = $jobExecutionClass;

        // ... there is an ugly fix related to PIM-5589...
        // by default, doctrine creates an `ORM\EntityRepository` to query on entities
        // you can configure a custom repository in the doctrine mapping of an entity
        // we can override these custom repositories in projects by using `ResolveTargetRepositorySubscriber`
        // these changes are allowed by the Doctrine lifecycle events
        // when configuring connections in a 'classic' way, ie by defining these in the config.yml of the application
        // the Symfony Bridge uses the compiler pass RegisterEventListenersAndSubscribersPass to configure all the
        // event listener logic.
        // here, we directly create a new Doctrine connection without benefiting on this default behavior and the
        // repository is never customized, so we simulate the injection of the custom repository
        $metadata = $entityManager->getClassMetadata($jobInstanceClass);
        $metadata->customRepositoryClassName = $jobInstanceRepoClass;
        // the good way to fix this is to configure the new connection in a more classic way and to re-write parts of
        // BatchBundle to avoid job instance merges and other weirdnesses
        // ... end of the ugly fix ...

        $this->batchSize = $batchSize;
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
    public function createJobExecution(JobInstance $jobInstance, JobParameters $jobParameters)
    {
        if (null !== $jobInstance->getId()) {
            $jobInstance = $this->jobManager->merge($jobInstance);
        } else {
            $this->jobManager->persist($jobInstance);
        }

        /** @var JobExecution */
        $jobExecution = new $this->jobExecutionClass();
        $jobExecution->setJobInstance($jobInstance);
        $jobExecution->setJobParameters($jobParameters);

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
     * {@inheritdoc}
     */
    public function getLastJobExecution(JobInstance $jobInstance, $status)
    {
        return $this->jobManager->createQueryBuilder()
            ->select('j')
            ->from($this->jobExecutionClass, 'j')
            ->where('j.jobInstance = :job_instance')
            ->andWhere('j.status = :status')
            ->setParameter('job_instance', $jobInstance->getId())
            ->setParameter('status', $status)
            ->orderBy('j.startTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findPurgeables($days)
    {
        $qb = $this->jobManager->createQueryBuilder()
            ->select('je')
            ->from($this->jobExecutionClass, 'je');

        $date = new \DateTime();
        $date->modify(sprintf('- %d days', $days));

        $qb->where(
            $qb->expr()->lt('je.endTime', ':date')
        )->setParameter('date', $date->format(self::DATETIME_FORMAT));

        $subQb = $this->jobManager->createQueryBuilder()
            ->select('MAX(je_max.id)')
            ->from($this->jobExecutionClass, 'je_max')
            ->where('je_max.status = :status')
            ->groupBy('je_max.jobInstance');

        $qb->andWhere(
            $qb->expr()->notIn('je.id', $subQb->getDQL())
        )->setParameter('status', BatchStatus::COMPLETED);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $jobsExecutions)
    {
        foreach ($jobsExecutions as $i => $jobsExecution) {
            $this->jobManager->remove($jobsExecution);

            if (0 === $i % $this->batchSize) {
                $this->jobManager->flush();
            }
        }
        $this->jobManager->flush();
    }

    /**
     * Ping the Server, if not available then reset the connection.
     * @author Cristian Quiroz <cq@amp.co>
     */
    protected function checkConnection()
    {
        $connection = $this->jobManager->getConnection();
        if ($this->pingConnection() === false) {
            $connection->close();
            $connection->connect();
        }
    }

    /**
     * Pings the server, returns false if it's not available.
     * There is a ping() method in Doctrine\DBAL\Connection in the doctrine/dbal package
     * as of 2.5.0, but  we are currently on 2.4.x
     * @author Cristian Quiroz <cq@amp.co>
     *
     * @return bool
     */
    protected function pingConnection()
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

    /**
     * To avoid memory leak, we insert the warnings directly in database without
     * creating a Warning entity (as it is cascade persist from the JobExecution,
     * there is no way to save them then detach them without huge BC break).
     *
     * Then we need to reinitialize the warnings persistent collection, so it is
     * not systematically empty, resulting in an always successful notification
     * at the end of the batch job.
     *
     * As the warnings are extra-lazy, in a persistent collection, this do not
     * cause a new memory leak.
     *
     * @param StepExecution $stepExecution
     * @param string        $reason
     * @param array         $reasonParameters
     * @param array         $item
     *
     * @todo Add interface on master (or find a proper way to do this as it is kind of a dirty hack).
     */
    public function insertWarning(
        StepExecution $stepExecution,
        $reason,
        $reasonParameters = [],
        $item = []
    ) {
        $sqlQuery = <<<SQL
INSERT INTO akeneo_batch_warning (step_execution_id, reason, reason_parameters, item)
VALUES (:step_execution_id, :reason, :reason_parameters, :item)
SQL;

        $connection = $this->jobManager->getConnection();

        $statement = $connection->prepare($sqlQuery);
        $statement->bindValue('step_execution_id', $stepExecution->getId());
        $statement->bindValue('reason', $reason);
        $statement->bindValue('reason_parameters', $reasonParameters, 'array');
        $statement->bindValue('item', $item, 'array');
        $statement->execute();

        if ($stepExecution->getWarnings() instanceof PersistentCollection) {
            $stepExecution->getWarnings()->setInitialized(false);
        }
    }
}

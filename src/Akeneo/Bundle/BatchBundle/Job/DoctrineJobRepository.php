<?php

namespace Akeneo\Bundle\BatchBundle\Job;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

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
        $this->jobManager->persist($jobExecution);
        $this->jobManager->flush($jobExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStepExecution(StepExecution $stepExecution)
    {
        $this->jobManager->persist($stepExecution);
        $this->jobManager->flush($stepExecution);
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
     * @param string        $warningName
     * @param string        $reason
     * @param array         $reasonParameters
     * @param array         $item
     *
     * @todo Add interface on master (or find a proper way to do this as it is kind of a dirty hack).
     */
    public function insertWarning(
        StepExecution $stepExecution,
        $warningName,
        $reason,
        $reasonParameters = [],
        $item = []
    ) {
        $sqlQuery = <<<SQL
INSERT INTO akeneo_batch_warning (step_execution_id, name, reason, reason_parameters, item)
VALUES (:step_execution_id, :name, :reason, :reason_parameters, :item)
SQL;

        $connection = $this->jobManager->getConnection();

        $statement = $connection->prepare($sqlQuery);
        $statement->bindValue('step_execution_id', $stepExecution->getId());
        $statement->bindValue('name', $warningName);
        $statement->bindValue('reason', $reason);
        $statement->bindValue('reason_parameters', $reasonParameters, 'array');
        $statement->bindValue('item', $item, 'array');
        $statement->execute();

        if ($stepExecution->getWarnings() instanceof PersistentCollection) {
            $stepExecution->getWarnings()->setInitialized(false);
        }
    }
}

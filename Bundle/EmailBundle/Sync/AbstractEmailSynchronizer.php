<?php

namespace Oro\Bundle\EmailBundle\Sync;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

abstract class AbstractEmailSynchronizer
{
    const SYNC_CODE_IN_PROCESS = 1;
    const SYNC_CODE_FAILURE = 2;
    const SYNC_CODE_SUCCESS = 3;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EmailEntityBuilder
     */
    protected $emailEntityBuilder;

    /**
     * @var EmailAddressManager
     */
    protected $emailAddressManager;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param EmailEntityBuilder $emailEntityBuilder
     * @param EmailAddressManager $emailAddressManager
     */
    protected function __construct(
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager
    ) {
        $this->em = $em;
        $this->emailEntityBuilder = $emailEntityBuilder;
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * Sets a logger
     *
     * @param LoggerInterface $log
     */
    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }

    /**
     * Performs a synchronization of emails for one email origin.
     * Algorithm how an email origin is selected see in findOriginToSync method.
     *
     * @param int $maxConcurrentTasks  The maximum number of synchronization jobs running in the same time
     * @param int $minExecPeriodInMin  The time interval (in minutes) a synchronization for
     *                                 the same email origin can be executed
     * @param int $maxExecTimeoutInMin The maximum time frame (in minutes) this synchronization tasks
     *                                 can spend at one run
     *                                 Set -1 to unlimited
     *                                 Defaults to -1
     * @param int $maxTasks            The maximum number of synchronization tasks to be executed
     *                                 Set -1 to unlimited
     *                                 Defaults to 1
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function sync($maxConcurrentTasks, $minExecPeriodInMin, $maxExecTimeoutInMin = -1, $maxTasks = 1)
    {
        $startTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->resetHangedOrigins();

        $maxExecTimeout = $maxExecTimeoutInMin > 0
            ? new \DateInterval('PT' . $maxExecTimeoutInMin . 'M')
            : false;
        $processedOrigins = array();
        $failedOriginIds = array();
        while (true) {
            if ($maxExecTimeout !== false) {
                $date = new \DateTime('now', new \DateTimeZone('UTC'));
                if ($date->sub($maxExecTimeout) >= $startTime) {
                    $this->log->notice('Exit because allocated time frame elapsed.');
                    break;
                }
            }

            $origin = $this->findOriginToSync($maxConcurrentTasks, $minExecPeriodInMin);
            if ($origin === null) {
                $this->log->notice('Exit because nothing to synchronise.');
                break;
            }

            if (isset($processedOrigins[$origin->getId()])) {
                $this->log->notice('Exit because all origins have been synchronised.');
                break;
            }

            $processedOrigins[$origin->getId()] = true;
            try {
                $this->doSyncOrigin($origin);
            } catch (\Exception $ex) {
                $failedOriginIds[] = $origin->getId();
            }

            if ($maxTasks > 0 && count($processedOrigins) >= $maxTasks) {
                $this->log->notice('Exit because the limit of tasks are reached.');
                break;
            }
        }

        if (!empty($failedOriginIds)) {
            throw new \Exception(
                sprintf('The email synchronization failed for the following origins: ', implode(', ', $failedOriginIds))
            );
        }
    }

    /**
     * Performs a synchronization of emails for the given email origin.
     *
     * @param int $originId
     * @throws \Exception
     */
    public function syncOrigin($originId)
    {
        $origin = $this->findOrigin($originId);
        if ($origin === null) {
            $this->log->notice('Exit because nothing to synchronise.');
        } else {
            $this->doSyncOrigin($origin);
        }
    }

    /**
     * Performs a synchronization of emails for the given email origin.
     *
     * @param EmailOrigin $origin
     * @throws \Exception
     */
    protected function doSyncOrigin(EmailOrigin $origin)
    {
        $processor = $this->createSynchronizationProcessor($origin);

        try {
            if ($this->changeOriginSyncState($origin, self::SYNC_CODE_IN_PROCESS)) {
                $synchronizedAt = new \DateTime('now', new \DateTimeZone('UTC'));
                $processor->process($origin);
                $this->changeOriginSyncState($origin, self::SYNC_CODE_SUCCESS, $synchronizedAt);
            } else {
                $this->log->notice('Skip because it is already in process.');
            }
        } catch (\Exception $ex) {
            try {
                $this->changeOriginSyncState($origin, self::SYNC_CODE_FAILURE);
            } catch (\Exception $innerEx) {
                // ignore any exception here
                $this->log->error(
                    sprintf('Cannot set the fail state. Error: %s.', $innerEx->getMessage()),
                    array($innerEx)
                );
            }

            $this->log->error(
                sprintf('The synchronization failed. Error: %s.', $ex->getMessage()),
                array($ex)
            );

            throw $ex;
        }
    }

    /**
     * Gets entity name implementing EmailOrigin
     *
     * @return string
     */
    abstract protected function getEmailOriginClass();

    /**
     * Creates a processor is used to synchronize emails
     *
     * @param object $origin An instance of class implementing EmailOrigin entity
     * @return AbstractEmailSynchronizationProcessor
     */
    abstract protected function createSynchronizationProcessor($origin);

    /**
     * Updates a state of the given email origin
     *
     * @param EmailOrigin $origin
     * @param int $syncCode Can be one of self::SYNC_CODE_* constants
     * @param \DateTime|null $synchronizedAt
     * @return bool true if the synchronization code was updated; false if no any changes are needed
     */
    protected function changeOriginSyncState(EmailOrigin $origin, $syncCode, $synchronizedAt = null)
    {
        $queryBuilder = $this->em->getRepository($this->getEmailOriginClass())
            ->createQueryBuilder('o')
            ->update()
            ->set('o.syncCode', ':code')
            ->set('o.syncCodeUpdatedAt', ':updated')
            ->where('o.id = :id')
            ->setParameter('code', $syncCode)
            ->setParameter('updated', new \DateTime('now', new \DateTimeZone('UTC')))
            ->setParameter('id', $origin->getId());
        if ($synchronizedAt !== null) {
            $queryBuilder
                ->set('o.synchronizedAt', ':synchronized')
                ->setParameter('synchronized', $synchronizedAt);
        }
        if ($syncCode === self::SYNC_CODE_IN_PROCESS) {
            $queryBuilder->andWhere('o.syncCode <> :code');
        }
        $affectedRows = $queryBuilder->getQuery()->execute();

        return $affectedRows > 0;
    }

    /**
     * Finds an email origin to be synchronised
     *
     * @param int $maxConcurrentTasks The maximum number of synchronization jobs running in the same time
     * @param int $minExecPeriodInMin The time interval (in minutes) a synchronization for
     *                                the same email origin can be executed
     * @return EmailOrigin
     */
    protected function findOriginToSync($maxConcurrentTasks, $minExecPeriodInMin)
    {
        $this->log->notice('Finding an email origin ...');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $border = clone $now;
        if ($minExecPeriodInMin > 0) {
            $border->sub(new \DateInterval('PT' . $minExecPeriodInMin . 'M'));
        }
        $min = clone $now;
        $min->sub(new \DateInterval('P1Y'));

        $repo = $this->em->getRepository($this->getEmailOriginClass());
        $query = $repo->createQueryBuilder('o')
            ->select(
                'o'
                . ', CASE WHEN o.syncCode = :inProcess THEN 0 ELSE 1 END AS HIDDEN p1'
                . ', (COALESCE(o.syncCode, 1000) * 100'
                . ' + (:now - COALESCE(o.syncCodeUpdatedAt, :min))'
                . ' / (CASE o.syncCode WHEN :success THEN 100 ELSE 1 END)) AS HIDDEN p2'
            )
            ->where('o.isActive = :isActive AND o.syncCodeUpdatedAt IS NULL OR o.syncCodeUpdatedAt <= :border')
            ->orderBy('p1, p2 DESC, o.syncCodeUpdatedAt')
            ->setParameter('inProcess', self::SYNC_CODE_IN_PROCESS)
            ->setParameter('success', self::SYNC_CODE_SUCCESS)
            ->setParameter('isActive', true)
            ->setParameter('now', $now)
            ->setParameter('min', $min)
            ->setParameter('border', $border)
            ->setMaxResults($maxConcurrentTasks + 1)
            ->getQuery();

        /** @var EmailOrigin[] $origins */
        $origins = $query->getResult();
        $result = null;
        foreach ($origins as $origin) {
            if ($origin->getSyncCode() !== self::SYNC_CODE_IN_PROCESS) {
                $result = $origin;
                break;
            }
        }

        if ($result === null) {
            if (!empty($origins)) {
                $this->log->notice('The maximum number of concurrent tasks is reached.');
            }
            $this->log->notice('An email origin was not found.');
        } else {
            $this->log->notice(sprintf('Found email origin id: %d.', $result->getId()));
        }

        return $result;
    }

    /**
     * Finds active email origin by its id
     *
     * @param int $originId
     * @return EmailOrigin|null
     */
    protected function findOrigin($originId)
    {
        $this->log->notice('Finding an email origin ...');

        $repo = $this->em->getRepository($this->getEmailOriginClass());
        $query = $repo->createQueryBuilder('o')
            ->where('o.isActive = :isActive AND o.id = :id')
            ->setParameter('isActive', true)
            ->setParameter('id', $originId)
            ->setMaxResults(1)
            ->getQuery();
        $origins = $query->getResult();

        /** @var EmailOrigin $result */
        $result = !empty($origins) ? $origins[0] : null;

        if ($result === null) {
            $this->log->notice('An email origin was not found.');
        } else {
            $this->log->notice(sprintf('Found email origin id: %d.', $result->getId()));
        }

        return $result;
    }

    /**
     * Marks outdated "In Process" origins as "Failure" if exist
     */
    protected function resetHangedOrigins()
    {
        $this->log->notice('Resetting hanged email origins ...');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $border = clone $now;
        $border->sub(new \DateInterval('P1D'));

        $repo = $this->em->getRepository($this->getEmailOriginClass());
        $query = $repo->createQueryBuilder('o')
            ->update()
            ->set('o.syncCode', ':failure')
            ->where('o.syncCode = :inProcess AND o.syncCodeUpdatedAt <= :border')
            ->setParameter('inProcess', self::SYNC_CODE_IN_PROCESS)
            ->setParameter('failure', self::SYNC_CODE_FAILURE)
            ->setParameter('border', $border)
            ->getQuery();

        $affectedRows = $query->execute();
        $this->log->notice(sprintf('Updated %d row(s).', $affectedRows));
    }
}

<?php

namespace Oro\Bundle\EmailBundle\Sync;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractEmailSynchronizer
{
    const SYNC_CODE_IN_PROCESS = 1;
    const SYNC_CODE_FAILURE = 2;
    const SYNC_CODE_SUCCESS = 3;

    /**
     * @var LoggerInterface
     */
    protected $log = null;

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
     * @param int $maxConcurrentTasks   The maximum number of synchronization jobs running in the same time
     * @param int $minExecIntervalInMin The minimum time interval (in minutes) between two synchronizations
     *                                  of the same email origin
     * @param int $maxExecTimeInMin     The maximum execution time (in minutes)
     *                                  Set -1 to unlimited
     *                                  Defaults to -1
     * @param int $maxTasks             The maximum number of email origins which can be synchronized
     *                                  Set -1 to unlimited
     *                                  Defaults to 1
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function sync($maxConcurrentTasks, $minExecIntervalInMin, $maxExecTimeInMin = -1, $maxTasks = 1)
    {
        if ($this->log === null) {
            $this->log = new NullLogger();
        }

        $startTime = $this->getCurrentUtcDateTime();
        $this->resetHangedOrigins();

        $maxExecTimeout = $maxExecTimeInMin > 0
            ? new \DateInterval('PT' . $maxExecTimeInMin . 'M')
            : false;
        $processedOrigins = array();
        $failedOriginIds = array();
        while (true) {
            if ($maxExecTimeout !== false) {
                $date = $this->getCurrentUtcDateTime();
                if ($date->sub($maxExecTimeout) >= $startTime) {
                    $this->log->notice('Exit because allocated time frame elapsed.');
                    break;
                }
            }

            $origin = $this->findOriginToSync($maxConcurrentTasks, $minExecIntervalInMin);
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
                sprintf(
                    'The email synchronization failed for the following origins: %s.',
                    implode(', ', $failedOriginIds)
                )
            );
        }
    }

    /**
     * Performs a synchronization of emails for the given email origins.
     *
     * @param int[] $originIds
     * @throws \Exception
     */
    public function syncOrigins(array $originIds)
    {
        if ($this->log === null) {
            $this->log = new NullLogger();
        }

        $failedOriginIds = array();
        foreach ($originIds as $originId) {
            $origin = $this->findOrigin($originId);
            if ($origin !== null) {
                try {
                    $this->doSyncOrigin($origin);
                } catch (\Exception $ex) {
                    $failedOriginIds[] = $origin->getId();
                }
            }
        }
        if (!empty($failedOriginIds)) {
            throw new \Exception(
                sprintf(
                    'The email synchronization failed for the following origins: %s.',
                    implode(', ', $failedOriginIds)
                )
            );
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
                $synchronizedAt = $this->getCurrentUtcDateTime();
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
                    array('exception' => $innerEx)
                );
            }

            $this->log->error(
                sprintf('The synchronization failed. Error: %s.', $ex->getMessage()),
                array('exception' => $ex)
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
            ->setParameter('updated', $this->getCurrentUtcDateTime())
            ->setParameter('id', $origin->getId());
        if ($synchronizedAt !== null) {
            $queryBuilder
                ->set('o.synchronizedAt', ':synchronized')
                ->setParameter('synchronized', $synchronizedAt);
        }
        if ($syncCode === self::SYNC_CODE_IN_PROCESS) {
            $queryBuilder->andWhere('(o.syncCode IS NULL OR o.syncCode <> :code)');
        }
        $affectedRows = $queryBuilder->getQuery()->execute();

        return $affectedRows > 0;
    }

    /**
     * Finds an email origin to be synchronised
     *
     * @param int $maxConcurrentTasks   The maximum number of synchronization jobs running in the same time
     * @param int $minExecIntervalInMin The minimum time interval (in minutes) between two synchronizations
     *                                  of the same email origin
     * @return EmailOrigin
     */
    protected function findOriginToSync($maxConcurrentTasks, $minExecIntervalInMin)
    {
        $this->log->notice('Finding an email origin ...');

        $now = $this->getCurrentUtcDateTime();
        $border = clone $now;
        if ($minExecIntervalInMin > 0) {
            $border->sub(new \DateInterval('PT' . $minExecIntervalInMin . 'M'));
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
        $this->log->notice(sprintf('Finding an email origin (id: %d) ...', $originId));

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

        $now = $this->getCurrentUtcDateTime();
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

    /**
     * Gets a DateTime object that is set to the current date and time in UTC.
     *
     * @return \DateTime
     */
    protected function getCurrentUtcDateTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}

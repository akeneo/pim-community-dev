<?php

namespace Oro\Bundle\ImapBundle\Sync;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Oro\Bundle\CronBundle\Command\Logger\LoggerInterface;
use Oro\Bundle\ImapBundle\Connector\ImapConfig;
use Oro\Bundle\ImapBundle\Connector\ImapConnectorFactory;
use Oro\Bundle\ImapBundle\Manager\ImapEmailManager;
use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;

class ImapEmailSynchronizer
{
    const SYNC_CODE_IN_PROCESS = 1;
    const SYNC_CODE_FAILURE = 2;
    const SYNC_CODE_SUCCESS = 3;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var ImapConnectorFactory
     */
    protected $connectorFactory;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EmailEntityBuilder
     */
    protected $emailEntityBuilder;

    /**
     * Constructor
     *
     * @param LoggerInterface $log
     * @param ImapConnectorFactory $connectorFactory
     * @param EntityManager $em
     * @param EmailEntityBuilder $emailEntityBuilder
     */
    public function __construct(
        LoggerInterface $log,
        ImapConnectorFactory $connectorFactory,
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder
    ) {
        $this->log = $log;
        $this->connectorFactory = $connectorFactory;
        $this->em = $em;
        $this->emailEntityBuilder = $emailEntityBuilder;
    }

    /**
     * Performs a synchronization of emails for one email origin.
     * Algorithm how an email origin is selected see in getOriginToSync method.
     *
     * @param int $maxConcurrentTasks The maximum number of synchronization jobs running in the same time
     * @param int $minExecPeriodInMin The time interval (in minutes) a synchronization for
     *                                the same email origin can be executed
     * @throws \Exception
     */
    public function sync($maxConcurrentTasks, $minExecPeriodInMin)
    {
        $this->resetHangedOrigins();

        $origin = $this->getOriginToSync($maxConcurrentTasks, $minExecPeriodInMin);
        if ($origin === null) {
            $this->log->notice('Exit because nothing to synchronise.');

            return;
        }

        $config = new ImapConfig(
            $origin->getHost(),
            $origin->getPort(),
            $origin->getSsl(),
            $origin->getUser(),
            $origin->getPassword()
        );
        $processor = new ImapEmailSynchronizationProcessor(
            $this->log,
            new ImapEmailManager($this->connectorFactory->createImapConnector($config)),
            $this->em,
            $this->emailEntityBuilder
        );

        try {
            $this->changeOriginSyncState($origin, self::SYNC_CODE_IN_PROCESS);
            $synchronizedAt = new \DateTime('now', new \DateTimeZone('UTC'));
            $processor->process($origin);
            $this->changeOriginSyncState($origin, self::SYNC_CODE_SUCCESS, $synchronizedAt);
        } catch (\Exception $ex) {
            try {
                $this->changeOriginSyncState($origin, self::SYNC_CODE_FAILURE);
            } catch (\Exception $innerEx) {
                // ignore any exception here
                $this->log->error(
                    sprintf('Cannot set the fail state. Error: %s.', $innerEx->getMessage())
                );
            }

            $this->log->error(
                sprintf('The synchronization failed. Error: %s.', $ex->getMessage())
            );

            throw $ex;
        }
    }

    /**
     * Updates a state of the given email origin
     *
     * @param ImapEmailOrigin $origin
     * @param int $syncCode Can be one of self::SYNC_CODE_* constants
     * @param \DateTime|null $synchronizedAt
     */
    protected function changeOriginSyncState(ImapEmailOrigin $origin, $syncCode, $synchronizedAt = null)
    {
        $queryBuilder = $this->em->getRepository('OroImapBundle:ImapEmailOrigin')
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
        $queryBuilder->getQuery()->execute();
    }

    /**
     * Finds an email origin to be synchronised
     *
     * @param int $maxConcurrentTasks The maximum number of synchronization jobs running in the same time
     * @param int $minExecPeriodInMin The time interval (in minutes) a synchronization for
     *                                the same email origin can be executed
     * @return ImapEmailOrigin
     */
    protected function getOriginToSync($maxConcurrentTasks, $minExecPeriodInMin)
    {
        $this->log->info('Finding an email origin ...');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $border = $minExecPeriodInMin > 0
            ? $now->sub(new \DateInterval('PT' . $minExecPeriodInMin . 'M'))
            : $now;

        $repo = $this->em->getRepository('OroImapBundle:ImapEmailOrigin');
        $query = $repo->createQueryBuilder('o')
            ->select(
                'o'
                . ', CASE WHEN o.syncCode = :inProcess THEN 0 ELSE 1 END AS HIDDEN p1'
                . ', (COALESCE(o.syncCode, 1000) * 100'
                . ' + (:now - o.syncCodeUpdatedAt) / (CASE o.syncCode WHEN :success THEN 100 ELSE 1 END)) AS HIDDEN p2'
            )
            ->where('o.syncCodeUpdatedAt IS NULL OR o.syncCodeUpdatedAt <= :border')
            ->orderBy('p1, p2 DESC, o.syncCodeUpdatedAt')
            ->setParameter('inProcess', self::SYNC_CODE_IN_PROCESS)
            ->setParameter('success', self::SYNC_CODE_SUCCESS)
            ->setParameter('now', $now)
            ->setParameter('border', $border)
            ->setMaxResults($maxConcurrentTasks + 1)
            ->getQuery();

        /** @var ImapEmailOrigin[] $origins */
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
                $this->log->info('The maximum number of concurrent tasks is reached.');
            }
            $this->log->info('An email origin was not found.');
        } else {
            $this->log->info(sprintf('Found email origin id: %d.', $result->getId()));
        }

        return $result;
    }

    /**
     * Marks outdated "In Process" origins as "Failure" if exist
     */
    protected function resetHangedOrigins()
    {
        $this->log->info('Resetting hanged email origins ...');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $border = $now->sub(new \DateInterval('P1D'));

        $repo = $this->em->getRepository('OroImapBundle:ImapEmailOrigin');
        $query = $repo->createQueryBuilder('o')
            ->update()
            ->set('o.syncCode', ':failure')
            ->where('o.syncCode = :inProcess AND o.syncCodeUpdatedAt <= :border')
            ->setParameter('inProcess', self::SYNC_CODE_IN_PROCESS)
            ->setParameter('failure', self::SYNC_CODE_FAILURE)
            ->setParameter('border', $border)
            ->getQuery();

        $affectedRows = $query->execute();
        $this->log->info(sprintf('Updated %d row(s).', $affectedRows));
    }
}

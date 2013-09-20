<?php

namespace Oro\Bundle\EmailBundle\Sync;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CronBundle\Command\Logger\LoggerInterface;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;

abstract class AbstractEmailSynchronizationProcessor
{
    const DB_BATCH_SIZE = 30;

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
     * @param LoggerInterface $log
     * @param EntityManager $em
     * @param EmailEntityBuilder $emailEntityBuilder
     * @param EmailAddressManager $emailAddressManager
     */
    public function __construct(
        LoggerInterface $log,
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager
    ) {
        $this->log = $log;
        $this->em = $em;
        $this->emailEntityBuilder = $emailEntityBuilder;
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * Performs a synchronization of emails for the given email origin.
     *
     * @param EmailOrigin $origin
     */
    abstract public function process(EmailOrigin $origin);

    /**
     * Gets a list of email addresses which have an owner
     *
     * @return string[]
     */
    protected function getKnownEmailAddresses()
    {
        $this->log->info('Loading known email addresses ...');

        $repo = $this->emailAddressManager->getEmailAddressRepository($this->em);
        $query = $repo->createQueryBuilder('a')
            ->select('a.email')
            ->where('a.hasOwner = ?1')
            ->setParameter(1, true)
            ->getQuery();
        $emailAddresses = $query->getArrayResult();

        $this->log->info(sprintf('Loaded %d email address(es).', count($emailAddresses)));

        return array_map(
            function ($el) {
                return $el['email'];
            },
            $emailAddresses
        );
    }
}

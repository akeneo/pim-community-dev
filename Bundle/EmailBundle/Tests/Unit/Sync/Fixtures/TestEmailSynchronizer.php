<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Sync\AbstractEmailSynchronizer;
use Oro\Bundle\EmailBundle\Sync\AbstractEmailSynchronizationProcessor;

class TestEmailSynchronizer extends AbstractEmailSynchronizer
{
    const EMAIL_ORIGIN_ENTITY = 'AcmeBundle:EmailOrigin';

    private $now;

    public function __construct(
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager
    ) {
        parent::__construct($em, $emailEntityBuilder, $emailAddressManager);
    }

    protected function getEmailOriginClass()
    {
        return self::EMAIL_ORIGIN_ENTITY;
    }

    protected function createSynchronizationProcessor($origin)
    {
        return new TestEmailSynchronizationProcessor(
            $this->log,
            $this->em,
            $this->emailEntityBuilder,
            $this->emailAddressManager
        );
    }

    protected function getCurrentUtcDateTime()
    {
        return $this->now;
    }

    public function setCurrentUtcDateTime(\DateTime $now)
    {
        $this->now = $now;
    }

    public function callDoSyncOrigin(EmailOrigin $origin)
    {
        $this->doSyncOrigin($origin);
    }

    public function callChangeOriginSyncState(EmailOrigin $origin, $syncCode, $synchronizedAt)
    {
        return $this->changeOriginSyncState($origin, $syncCode, $synchronizedAt);
    }

    public function callFindOriginToSync($maxConcurrentTasks, $minExecPeriodInMin)
    {
        return $this->findOriginToSync($maxConcurrentTasks, $minExecPeriodInMin);
    }

    public function callResetHangedOrigins()
    {
        $this->resetHangedOrigins();
    }
}

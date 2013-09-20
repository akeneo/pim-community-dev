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

    public function callChangeOriginSyncState(EmailOrigin $origin, $syncCode, $synchronizedAt)
    {
        $this->changeOriginSyncState($origin, $syncCode, $synchronizedAt);
    }

    public function callGetOriginToSync($maxConcurrentTasks, $minExecPeriodInMin)
    {
        return $this->getOriginToSync($maxConcurrentTasks, $minExecPeriodInMin);
    }

    public function callResetHangedOrigins()
    {
        $this->resetHangedOrigins();
    }
}

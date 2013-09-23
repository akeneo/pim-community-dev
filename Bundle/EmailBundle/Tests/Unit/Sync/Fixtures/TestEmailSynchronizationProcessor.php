<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Sync\Fixtures;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EmailBundle\Sync\AbstractEmailSynchronizationProcessor;
use Psr\Log\LoggerInterface;

class TestEmailSynchronizationProcessor extends AbstractEmailSynchronizationProcessor
{
    public function __construct(
        LoggerInterface $log,
        EntityManager $em,
        EmailEntityBuilder $emailEntityBuilder,
        EmailAddressManager $emailAddressManager
    ) {
        parent::__construct($log, $em, $emailEntityBuilder, $emailAddressManager);
    }

    public function process(EmailOrigin $origin)
    {
    }

    public function callGetKnownEmailAddresses()
    {
        return $this->getKnownEmailAddresses();
    }
}

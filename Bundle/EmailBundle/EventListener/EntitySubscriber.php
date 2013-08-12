<?php

namespace Oro\Bundle\EmailBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

class EntitySubscriber implements EventSubscriber
{
    /**
     * @var EmailAddressManager
     */
    protected $emailAddressManager;

    public function __construct(EmailAddressManager $emailAddressManager)
    {
        $this->emailAddressManager = $emailAddressManager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush
        );
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->emailAddressManager->handleOnFlush($event);
    }
}

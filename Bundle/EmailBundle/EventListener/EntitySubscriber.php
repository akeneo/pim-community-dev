<?php

namespace Oro\Bundle\EmailBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailOwnerManager;

class EntitySubscriber implements EventSubscriber
{
    /**
     * @var EmailOwnerManager
     */
    protected $emailOwnerManager;

    public function __construct(EmailOwnerManager $emailOwnerManager)
    {
        $this->emailOwnerManager = $emailOwnerManager;
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
        $this->emailOwnerManager->handleOnFlush($event);
    }
}

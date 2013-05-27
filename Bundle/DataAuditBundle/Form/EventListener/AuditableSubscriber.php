<?php

namespace Oro\Bundle\DataAuditBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\DataAuditBundle\Entity\AuditableInterface;

class AuditableSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_BIND => 'postBind',
        );
    }

    public function postBind(FormEvent $event)
    {
        /* @var $entity AuditableInterface */
        $entity = $event->getData();

        if (is_null($entity) || !$entity instanceof AuditableInterface) {
            return;
        }

        $entity->setAuditData();
    }
}

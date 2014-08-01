<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ChangeSet;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent;

/**
 * Subscriber that handle product values changeset metadata
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetadataSubscriber implements EventSubscriberInterface
{
    /** @staticvar string */
    const KEY = '__context__';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ChangeSetEvents::PREPARE_CHANGE => 'addMetadata',
            ProductDraftEvents::PRE_APPROVE => 'removeMetadata',
        ];
    }

    /**
     * Add value change metadata in the changeset
     *
     * @param ChangeSetEvent $event
     */
    public function addMetadata(ChangeSetEvent $event)
    {
        if (null === $changes = $event->getChangeSet()) {
            return;
        }

        $value = $event->getValue();
        $changes = array_merge(
            $changes,
            [
                self::KEY => [
                    'attribute' => $value->getAttribute()->getCode(),
                    'locale' => $value->getLocale(),
                    'scope' => $value->getScope(),
                ]
            ]
        );

        $event->setChangeSet($changes);
    }

    /**
     * Remove metadata before applying proposition changes
     *
     * @param ProductDraftEvent $event
     */
    public function removeMetadata(ProductDraftEvent $event)
    {
        $proposition = $event->getProposition();
        $changes = $proposition->getChanges();

        foreach (array_keys($changes) as $key) {
            unset($changes[$key][self::KEY]);
        }

        $proposition->setChanges($changes);
    }
}

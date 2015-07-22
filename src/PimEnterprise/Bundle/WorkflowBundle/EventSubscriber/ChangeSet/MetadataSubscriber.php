<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ChangeSet;

use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber that handle product values changeset metadata
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
                    'locale'    => $value->getLocale(),
                    'scope'     => $value->getScope(),
                ]
            ]
        );

        $event->setChangeSet($changes);
    }

    /**
     * Remove metadata before applying product draft changes
     *
     * @param ProductDraftEvent $event
     */
    public function removeMetadata(ProductDraftEvent $event)
    {
        $productDraft = $event->getProductDraft();
        $changes = $productDraft->getChanges();

        foreach (array_keys($changes) as $key) {
            unset($changes[$key][self::KEY]);
        }

        $productDraft->setChanges($changes);
    }
}

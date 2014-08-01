<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * Mark a product draft as in progress before updating it
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MarkInProgressSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::PRE_UPDATE => 'markAsInProgress',
        ];
    }

    /**
     * Mark the product draft as in progress
     *
     * @param ProductDraftEvent $event
     */
    public function markAsInProgress(ProductDraftEvent $event)
    {
        $event
            ->getProductDraft()
            ->setStatus(ProductDraft::IN_PROGRESS);
    }
}

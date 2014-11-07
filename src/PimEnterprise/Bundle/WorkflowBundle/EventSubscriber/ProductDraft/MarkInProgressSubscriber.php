<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Mark a product draft as in progress before updating it
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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

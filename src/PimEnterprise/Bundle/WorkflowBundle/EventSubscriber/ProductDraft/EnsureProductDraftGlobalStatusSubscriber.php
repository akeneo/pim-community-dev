<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Akeneo\Component\StorageUtils\StorageEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber guarantees the coherence between partial review statuses and global status of a product draft.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class EnsureProductDraftGlobalStatusSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'ensureGlobalStatus'];
    }

    /**
     * Ensure that the global status of a product draft is set
     * to "in progress" if it does not contain changes to review anymore
     *
     * @param GenericEvent $event
     */
    public function ensureGlobalStatus(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (!$productDraft instanceof ProductDraftInterface) {
            return;
        }

        if (!$productDraft->hasReviewStatus(ProductDraftInterface::CHANGE_TO_REVIEW)) {
            $productDraft->setStatus(ProductDraftInterface::IN_PROGRESS);
        }
    }
}

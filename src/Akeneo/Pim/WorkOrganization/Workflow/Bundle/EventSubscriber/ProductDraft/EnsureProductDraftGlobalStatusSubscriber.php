<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
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
     * Ensure that the global status of a product draft is set to :
     * - "ready" if it contains changes to review only
     * - "in progress" if it contains draft changes only
     *
     * @param GenericEvent $event
     */
    public function ensureGlobalStatus(GenericEvent $event)
    {
        $productDraft = $event->getSubject();

        if (!$productDraft instanceof EntityWithValuesDraftInterface || !$productDraft->hasChanges()) {
            return;
        }

        if ($productDraft->areAllReviewStatusesTo(EntityWithValuesDraftInterface::CHANGE_DRAFT)) {
            $productDraft->markAsInProgress();
        }

        if ($productDraft->areAllReviewStatusesTo(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)) {
            $productDraft->markAsReady();
        }
    }
}

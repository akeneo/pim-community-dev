<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EnsureProductDraftGlobalStatusSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_pre_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'ensureGlobalStatus']);
    }

    function it_sets_global_status_of_a_product_draft_to_in_progress_if_no_changes_to_review_left(
        GenericEvent $event,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->hasChanges()->willReturn(true);
        $productDraft->areAllReviewStatusesTo(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn(false);
        $productDraft->areAllReviewStatusesTo(EntityWithValuesDraftInterface::CHANGE_DRAFT)->willReturn(true);
        $productDraft->markAsInProgress()->shouldBeCalled();
        $productDraft->markAsReady()->shouldNotBeCalled();

        $this->ensureGlobalStatus($event);
    }

    function it_sets_global_status_of_a_product_draft_to_ready_if_only_changes_to_review_left(
        GenericEvent $event,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->hasChanges()->willReturn(true);
        $productDraft->areAllReviewStatusesTo(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn(true);
        $productDraft->areAllReviewStatusesTo(EntityWithValuesDraftInterface::CHANGE_DRAFT)->willReturn(false);
        $productDraft->markAsInProgress()->shouldNotBeCalled();
        $productDraft->markAsReady()->shouldBeCalled();

        $this->ensureGlobalStatus($event);
    }
}

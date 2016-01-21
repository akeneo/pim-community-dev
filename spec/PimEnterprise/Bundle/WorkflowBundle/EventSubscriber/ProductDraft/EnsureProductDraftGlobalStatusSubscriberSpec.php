<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EnsureProductDraftGlobalStatusSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'ensureGlobalStatus']);
    }

    function it_sets_global_status_of_a_product_draft_to_in_progress_if_no_changes_to_review_left(
        GenericEvent $event,
        ProductDraftInterface $productDraft
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->hasReviewStatus(ProductDraftInterface::CHANGE_TO_REVIEW)->willReturn(false);
        $productDraft->setStatus(ProductDraftInterface::IN_PROGRESS)->shouldBeCalled();

        $this->ensureGlobalStatus($event);
    }

    function it_does_not_change_global_status_of_a_product_draft_if_some_changes_to_review_left(
        GenericEvent $event,
        ProductDraftInterface $productDraft
    ) {
        $event->getSubject()->willReturn($productDraft);
        $productDraft->hasReviewStatus(ProductDraftInterface::CHANGE_TO_REVIEW)->willReturn(true);
        $productDraft->setStatus(ProductDraftInterface::IN_PROGRESS)->shouldNotBeCalled();

        $this->ensureGlobalStatus($event);
    }
}

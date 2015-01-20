<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

class MarkInProgressSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_pre_set_changes_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::PRE_UPDATE => 'markAsInProgress',
        ]);
    }

    function it_sets_updated_product_draft_as_in_progress(
        ProductDraftEvent $event,
        ProductDraft $productDraft
    ) {
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->setStatus(ProductDraft::IN_PROGRESS)->shouldBeCalled();

        $this->markAsInProgress($event);
    }
}

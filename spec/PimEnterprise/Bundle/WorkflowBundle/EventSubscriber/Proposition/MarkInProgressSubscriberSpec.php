<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class MarkInProgressSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_pre_set_changes_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PropositionEvents::PRE_UPDATE => 'markAsInProgress',
        ]);
    }

    function it_sets_updated_proposition_as_in_progress(
        PropositionEvent $event,
        Proposition $proposition
    ) {
        $event->getProposition()->willReturn($proposition);
        $proposition->setStatus(Proposition::IN_PROGRESS)->shouldBeCalled();

        $this->markAsInProgress($event);
    }
}

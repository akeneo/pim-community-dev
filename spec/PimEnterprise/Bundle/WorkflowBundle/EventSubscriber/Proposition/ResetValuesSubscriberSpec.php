<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class ResetValuesSubscriberSpec extends ObjectBehavior
{
    function let(ChangesCollectorInterface $collector)
    {
        $this->beConstructedWith($collector);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_pre_set_changes_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PropositionEvents::PRE_UPDATE => ['reset', 128],
        ]);
    }

    function it_removes_values_from_proposition_that_are_no_more_detected_as_changed(
        $collector,
        PropositionEvent $event,
        Proposition $proposition
    ) {
        $event->getProposition()->willReturn($proposition);
        $proposition->getChanges()->willReturn([
            'values' => [
                'foo' => [
                    'media' => [
                        'filename' => 'foo.jpg'
                    ],
                ],
                'bar' => [
                    'text' => 'BAR',
                ]
            ]
        ]);

        $collector->getKeysToRemove()->willReturn(['bar']);

        $proposition
            ->setChanges([
                'values' => [
                    'foo' => [
                        'media' => [
                            'filename' => 'foo.jpg',
                        ],
                    ],
                ]
            ])
            ->shouldBeCalled();

        $this->reset($event);
    }
}

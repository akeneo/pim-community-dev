<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class MergeValuesSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_pre_set_changes_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PropositionEvents::PRE_UPDATE => 'merge',
        ]);
    }

    function it_merges_values_that_are_not_present_in_the_new_value(
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

        $event->getChanges()->willReturn([
            'values' => [
                'bar' => [
                    'text' => 'Bar',
                ]
            ]
        ]);

        $proposition
            ->setChanges([
                'values' => [
                    'foo' => [
                        'media' => [
                            'filename' => 'foo.jpg',
                        ],
                    ],
                    'bar' => [
                        'text' => 'Bar',
                    ]
                ]
            ])
            ->shouldBeCalled();

        $this->merge($event);
    }
}

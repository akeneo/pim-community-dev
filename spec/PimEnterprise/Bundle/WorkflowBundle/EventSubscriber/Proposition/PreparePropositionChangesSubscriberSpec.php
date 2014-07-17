<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class PreparePropositionChangesSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_3_times_to_proposition_pre_update_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PropositionEvents::PRE_UPDATE => [
                ['keepMedia', 128],
                ['mergeValues', 64],
                ['removeNullValues', 0],
            ],
        ]);
    }

    function it_keeps_media_between_proposition_updates(PropositionEvent $event, Proposition $proposition)
    {
        $event->getProposition()->willReturn($proposition);
        $proposition->getChanges()->willReturn([
            'values' => [
                'foo' => [
                    'varchar' => 'Foo',
                ],
                'bar' => [
                    'media' => 'Bar.jpg',
                ],
            ],
        ]);
        $event->getChanges()->willReturn([
            'values' => [
                'foo' => [
                    'varchar' => 'Foo',
                ],
            ]
        ]);

        $event
            ->setChanges([
                'values' => [
                    'foo' => [
                        'varchar' => 'Foo',
                    ],
                    'bar' => [
                        'media' => 'Bar.jpg',
                    ]
                ],
            ])
            ->shouldBeCalled();

        $this->keepMedia($event);
    }

    function it_merges_changes_from_current_proposition_with_submitted_changes(
        PropositionEvent $event,
        Proposition $proposition
    ) {
        $event->getProposition()->willReturn($proposition);
        $proposition->getChanges()->willReturn([
            'values' => [
                'foo_en_US' => [
                    'varchar' => 'The Foo',
                ],
            ],
        ]);
        $event->getChanges()->willReturn([
            'values' => [
                'foo_fr_FR' => [
                    'varchar' => 'Le Foo',
                ],
            ]
        ]);

        $event
            ->setChanges([
                'values' => [
                    'foo_en_US' => [
                        'varchar' => 'The Foo',
                    ],
                    'foo_fr_FR' => [
                        'varchar' => 'Le Foo',
                    ],
                ],
            ])
            ->shouldBeCalled();

        $this->mergeValues($event);
    }

    function it_unsets_values_for_which_no_changes_were_detected(
        PropositionEvent $event,
        Proposition $proposition
    ) {
        $event->getChanges()->willReturn([
            'values' => [
                'foo_en_US' => [
                    'varchar' => 'The Foo',
                ],
                'bar' => null,
                'foo_fr_FR' => [
                    'varchar' => 'Le Foo',
                ],
            ]
        ]);

        $event
            ->setChanges([
                'values' => [
                    'foo_en_US' => [
                        'varchar' => 'The Foo',
                    ],
                    'foo_fr_FR' => [
                        'varchar' => 'Le Foo',
                    ],
                ],
            ])
            ->shouldBeCalled();

        $this->removeNullValues($event);
    }
}

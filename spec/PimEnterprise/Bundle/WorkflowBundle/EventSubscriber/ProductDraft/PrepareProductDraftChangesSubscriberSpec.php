<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

class PrepareProductDraftChangesSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_3_times_to_product_draft_pre_update_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::PRE_UPDATE => [
                ['keepMedia', 128],
                ['mergeValues', 64],
                ['removeNullValues', 0],
                ['cleanEmptyChangeSet', -128],
                ['sortValues', -128],
            ],
        ]);
    }

    function it_keeps_media_between_product_draft_updates(ProductDraftEvent $event, ProductDraft $productDraft)
    {
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn([
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

    function it_merges_changes_from_current_product_draft_with_submitted_changes(
        ProductDraftEvent $event,
        ProductDraft $productDraft
    ) {
        $event->getProductDraft()->willReturn($productDraft);
        $productDraft->getChanges()->willReturn([
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
        ProductDraftEvent $event
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

    function it_sorts_values(
        ProductDraftEvent $event
    ) {
        $event->getChanges()->willReturn([
            'values' => [
                'foo_en_US' => [
                    'varchar' => 'The Foo',
                ],
                'bar' => [
                    'varchar' => 'Bar',
                ],
                'foo_fr_FR' => [
                    'varchar' => 'Le Foo',
                ],
            ]
        ]);

        $event
            ->setChanges([
                'values' => [
                    'bar' => [
                        'varchar' => 'Bar',
                    ],
                    'foo_fr_FR' => [
                        'varchar' => 'Le Foo',
                    ],
                    'foo_en_US' => [
                        'varchar' => 'The Foo',
                    ],
                ],
            ])
            ->shouldBeCalled();

        $this->sortValues($event);
    }

    function it_cleans_empty_change_set(
        ProductDraftEvent $event
    ) {
        $event->getChanges()->willReturn([
            'values' => []
        ]);

        $event->setChanges([])->shouldBeCalled();

        $this->cleanEmptyChangeSet($event);
    }
}

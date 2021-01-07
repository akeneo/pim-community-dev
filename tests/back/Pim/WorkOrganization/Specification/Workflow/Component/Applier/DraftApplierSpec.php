<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Applier;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplier;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DraftApplierSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
        IdentifiableObjectRepositoryInterface $repository
    ) {
        $this->beConstructedWith($propertySetter, $dispatcher, $repository);
    }

    function it_is_a_applier()
    {
        $this->shouldBeAnInstanceOf(DraftApplier::class);
    }

    function it_does_not_apply_a_draft_without_values(
        $propertySetter,
        $dispatcher,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::PRE_APPLY
            )
            ->shouldBeCalled();

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::POST_APPLY
            )
            ->shouldNotBeCalled();

        $productDraft->getChanges()->willReturn([]);

        $this->applyAllChanges($product, $productDraft);
    }

    function it_applies_changes_to_review_of_a_draft(
        $propertySetter,
        $dispatcher,
        $repository,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $fakeAttribute
    ) {
        $productDraft->getChangesToReview()->willReturn([
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'data' => 'Test'],
                ],
                'description' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Description EN ecommerce'],
                    ['scope' => 'print',     'locale' => 'en_US', 'data' => 'Description EN print'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Description FR ecommerce']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                ],
                'description' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'print',     'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'print',     'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_DRAFT]
                ]
            ]
        ]);
        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::PRE_APPLY
            )
            ->shouldBeCalled();

        $propertySetter->setData($product, 'name', 'Test', [
            'locale' => null, 'scope' => null
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description EN ecommerce', [
            'locale' => 'en_US', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description EN print', [
            'locale' => 'en_US', 'scope' => 'print'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description FR ecommerce', [
            'locale' => 'fr_FR', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description FR print', [
            'locale' => 'fr_FR', 'scope' => 'print'
        ])->shouldNotBeCalled();

        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::POST_APPLY
            )
            ->shouldBeCalled();

        $repository->findOneByIdentifier(Argument::any())->willReturn($fakeAttribute);

        $this->applyToReviewChanges($product, $productDraft);
    }

    function it_applies_all_changes_of_a_draft(
        $propertySetter,
        $dispatcher,
        $repository,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $fakeAttribute
    ) {
        $productDraft->getChanges()->willReturn([
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'data' => 'Test'],
                ],
                'description' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Description EN ecommerce'],
                    ['scope' => 'print',     'locale' => 'en_US', 'data' => 'Description EN print'],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Description FR ecommerce'],
                    ['scope' => 'print',     'locale' => 'fr_FR', 'data' => 'Description FR print']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                ],
                'description' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'print',     'locale' => 'en_US', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW],
                    ['scope' => 'print',     'locale' => 'fr_FR', 'status' => EntityWithValuesDraftInterface::CHANGE_DRAFT]
                ]
            ]
        ]);
        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::PRE_APPLY
            )
            ->shouldBeCalled();

        $propertySetter->setData($product, 'name', 'Test', [
            'locale' => null, 'scope' => null
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description EN ecommerce', [
            'locale' => 'en_US', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description EN print', [
            'locale' => 'en_US', 'scope' => 'print'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description FR ecommerce', [
            'locale' => 'fr_FR', 'scope' => 'ecommerce'
        ])->shouldBeCalled();
        $propertySetter->setData($product, 'description', 'Description FR print', [
            'locale' => 'fr_FR', 'scope' => 'print'
        ])->shouldBeCalled();

        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::POST_APPLY
            )
            ->shouldBeCalled();

        $repository->findOneByIdentifier(Argument::any())->willReturn($fakeAttribute);

        $this->applyAllChanges($product, $productDraft);
    }
}

<?php

namespace spec\PimEnterprise\Component\Workflow\Applier;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Applier\DraftApplier;
use PimEnterprise\Component\Workflow\Event\EntityWithValuesDraftEvents;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
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
                EntityWithValuesDraftEvents::PRE_APPLY,
                Argument::type(GenericEvent::class)
            )
            ->shouldBeCalled();

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $dispatcher
            ->dispatch(
                EntityWithValuesDraftEvents::POST_APPLY,
                Argument::type(GenericEvent::class)
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
                EntityWithValuesDraftEvents::PRE_APPLY,
                Argument::type(GenericEvent::class)
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
                EntityWithValuesDraftEvents::POST_APPLY,
                Argument::type(GenericEvent::class)
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
                EntityWithValuesDraftEvents::PRE_APPLY,
                Argument::type(GenericEvent::class)
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
                EntityWithValuesDraftEvents::POST_APPLY,
                Argument::type(GenericEvent::class)
            )
            ->shouldBeCalled();

        $repository->findOneByIdentifier(Argument::any())->willReturn($fakeAttribute);

        $this->applyAllChanges($product, $productDraft);
    }
}

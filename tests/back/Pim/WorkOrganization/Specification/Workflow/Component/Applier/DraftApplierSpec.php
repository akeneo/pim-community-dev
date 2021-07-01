<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Applier;

use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplier;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
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

    function it_is_an_applier()
    {
        $this->shouldBeAnInstanceOf(DraftApplier::class);
    }

    function it_does_not_apply_a_draft_without_values(
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
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
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
        AttributeRepositoryInterface $repository,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $fakeAttribute
    ) {
        $productDraft->getChangesToReview()->willReturn(
            [
                'values' => [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'data' => 'Test'],
                    ],
                    'description' => [
                        ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Description EN ecommerce'],
                        ['scope' => 'print', 'locale' => 'en_US', 'data' => 'Description EN print'],
                        ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Description FR ecommerce'],
                    ],
                ],
                'review_statuses' => [
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'en_US',
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                        [
                            'scope' => 'print',
                            'locale' => 'en_US',
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                        [
                            'scope' => 'print',
                            'locale' => 'fr_FR',
                            'status' => EntityWithValuesDraftInterface::CHANGE_DRAFT,
                        ],
                    ],
                ],
            ]
        );
        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::PRE_APPLY
            )
            ->shouldBeCalled();

        $propertySetter->setData(
            $product,
            'name',
            'Test',
            [
                'locale' => null,
                'scope' => null,
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description EN ecommerce',
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description EN print',
            [
                'locale' => 'en_US',
                'scope' => 'print',
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description FR ecommerce',
            [
                'locale' => 'fr_FR',
                'scope' => 'ecommerce',
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description FR print',
            [
                'locale' => 'fr_FR',
                'scope' => 'print',
            ]
        )->shouldNotBeCalled();

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
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
        AttributeRepositoryInterface $repository,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $fakeAttribute
    ) {
        $productDraft->getChanges()->willReturn(
            [
                'values' => [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'data' => 'Test'],
                    ],
                    'description' => [
                        ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'Description EN ecommerce'],
                        ['scope' => 'print', 'locale' => 'en_US', 'data' => 'Description EN print'],
                        ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'Description FR ecommerce'],
                        ['scope' => 'print', 'locale' => 'fr_FR', 'data' => 'Description FR print'],
                    ],
                ],
                'review_statuses' => [
                    'name' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                    ],
                    'description' => [
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'en_US',
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                        [
                            'scope' => 'print',
                            'locale' => 'en_US',
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                        [
                            'scope' => 'ecommerce',
                            'locale' => 'fr_FR',
                            'status' => EntityWithValuesDraftInterface::CHANGE_TO_REVIEW,
                        ],
                        [
                            'scope' => 'print',
                            'locale' => 'fr_FR',
                            'status' => EntityWithValuesDraftInterface::CHANGE_DRAFT,
                        ],
                    ],
                ],
            ]
        );
        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::PRE_APPLY
            )
            ->shouldBeCalled();

        $propertySetter->setData(
            $product,
            'name',
            'Test',
            [
                'locale' => null,
                'scope' => null,
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description EN ecommerce',
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description EN print',
            [
                'locale' => 'en_US',
                'scope' => 'print',
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description FR ecommerce',
            [
                'locale' => 'fr_FR',
                'scope' => 'ecommerce',
            ]
        )->shouldBeCalled();
        $propertySetter->setData(
            $product,
            'description',
            'Description FR print',
            [
                'locale' => 'fr_FR',
                'scope' => 'print',
            ]
        )->shouldBeCalled();

        $dispatcher
            ->dispatch(
                Argument::type(GenericEvent::class),
                EntityWithValuesDraftEvents::POST_APPLY
            )
            ->shouldBeCalled();

        $repository->findOneByIdentifier(Argument::any())->willReturn($fakeAttribute);

        $this->applyAllChanges($product, $productDraft);
    }

    function it_does_not_apply_changes_if_the_data_is_not_valid(
        PropertySetterInterface $propertySetter,
        EntityWithValuesInterface $product,
        IdentifiableObjectRepositoryInterface $repository,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $productDraft->getChanges()->willReturn(
            [
                'values' => [
                    'attribute_1' => [
                        ['scope' => null, 'locale' => null, 'data' => 'something with a wrong format'],
                    ],
                    'attribute_2' => [
                        ['scope' => null, 'locale' => null, 'data' => 'some good data'],
                    ],
                ],
            ]
        );
        $repository->findOneByIdentifier('attribute_1')->shouldBeCalled()->willReturn(new Attribute());
        $repository->findOneByIdentifier('attribute_2')->shouldBeCalled()->willReturn(new Attribute());

        $propertySetter->setData(
            $product,
            'attribute_1',
            'something with a wrong format',
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled()->willThrow(
            InvalidPropertyTypeException::booleanExpected(
                'attribute_1',
                DraftApplier::class,
                'something with a wrong format'
            )
        );
        $propertySetter->setData(
            $product,
            'attribute_2',
            'some good data',
            ['scope' => null, 'locale' => null]
        )->shouldBeCalled();

        $this->shouldNotThrow(\Throwable::class)->during('applyAllChanges', [$product, $productDraft]);
    }
}

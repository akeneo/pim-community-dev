<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Builder\EntityWithValuesDraftBuilder;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\ProductDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithValuesDraftBuilderSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        GetAttributes $getAttributes,
        ProductDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $entityWithValuesDraftRepository,
        WriteValueCollectionFactory $valueCollectionFactory,
        ValueFactory $valueFactory
    ) {
        $this->beConstructedWith(
            $normalizer,
            $comparatorRegistry,
            $getAttributes,
            $factory,
            $entityWithValuesDraftRepository,
            $valueCollectionFactory,
            $valueFactory
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EntityWithValuesDraftBuilder::class);
    }

    function it_builds_a_simple_product_draft_when_submitted_data_is_different_from_product_data(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $getAttributes,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $product,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ComparatorInterface $textComparator,
        EntityWithValuesDraftInterface $productDraft,
        WriteValueCollection $newValuesCollection,
        WriteValueCollection $originalValuesCollection
    ) {
        $product->isVariant()->willReturn(false);
        $rawValues = [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my product'
                ]
            ]
        ];

        $newValuesCollection->add($textValue);
        $product->getValuesForVariation()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $product->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'my product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $textAttribute = new Attribute('name', 'text', [], false, false, null, null, false, 'text', []);
        $getAttributes->forCode('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->createByCheckingData($textAttribute, null, null, 'product')->willReturn($newTextValue);

        $newTextValue->getAttributeCode()->willReturn('text');

        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScopeCode()->willReturn(null);
        $newTextValue->getLocaleCode()->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new WriteValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $draftSource = new DraftSource('pim', 'PIM', 'mary', 'Mary Smith');

        $this->build($product, $draftSource)->shouldReturn($productDraft);
    }

    function it_builds_a_simple_product_draft_when_submitted_data_is_new(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $getAttributes,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $product,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ComparatorInterface $textComparator,
        EntityWithValuesDraftInterface $productDraft,
        WriteValueCollection $newValuesCollection,
        WriteValueCollection $originalValuesCollection
    ) {
        $product->isVariant()->willReturn(false);
        $rawValues = [];

        $newValuesCollection->add($textValue);
        $product->getValuesForVariation()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $product->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([]);

        $textAttribute = new Attribute('name', 'text', [], false, false, null, null, false, 'text', []);
        $getAttributes->forCode('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            []
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->createByCheckingData($textAttribute, null, null, 'product')->willReturn($newTextValue);
        $newTextValue->getAttributeCode()->willReturn('text');
        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScopeCode()->willReturn(null);
        $newTextValue->getLocaleCode()->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new WriteValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $draftSource = new DraftSource('pim', 'PIM', 'mary', 'Mary Smith');

        $this->build($product, $draftSource)->shouldReturn($productDraft);
    }

    function it_does_not_build_a_simple_product_draft_if_submitted_data_is_the_same_as_product_data(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $getAttributes,
        ProductInterface $product,
        ValueInterface $textValue,
        ComparatorInterface $textComparator,
        WriteValueCollection $newValuesCollection,
        WriteValueCollection $originalValuesCollection
    ) {
        $product->isVariant()->willReturn(false);
        $rawValues = [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my product'
                ]
            ]
        ];

        $newValuesCollection->add($textValue);
        $product->getValuesForVariation()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'my product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $product->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'my product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $textAttribute = new Attribute('name', 'text', [], false, false, null, null, false, 'text', []);
        $getAttributes->forCode('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'my product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(null);

        $draftSource = new DraftSource('pim', 'PIM', 'mary', 'Mary Smith');

        $this->build($product, $draftSource)->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_attribute_does_not_exist(
        $normalizer,
        $valueCollectionFactory,
        $getAttributes,
        ProductInterface $product,
        ValueInterface $textValue,
        WriteValueCollection $newValuesCollection,
        WriteValueCollection $originalValuesCollection
    ) {
        $product->isVariant()->willReturn(false);
        $rawValues = [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my product'
                ]
            ]
        ];

        $newValuesCollection->add($textValue);
        $product->getValuesForVariation()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $product->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'my product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $getAttributes->forCode('name')->willReturn(null);

        $draftSource = new DraftSource('pim', 'PIM', 'mary', 'Mary Smith');

        $this->shouldThrow(
            new \LogicException('Cannot find attribute with code "name".')
        )->during('build', [$product, $draftSource]);
    }

    function it_builds_a_variant_product_draft_when_submitted_data_is_different_from_product_data(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $getAttributes,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $variantProduct,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ComparatorInterface $textComparator,
        EntityWithValuesDraftInterface $productDraft,
        WriteValueCollection $newValuesCollection,
        WriteValueCollection $originalValuesCollection
    ) {
        $variantProduct->isVariant()->willReturn(true);
        $rawValues = [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my product'
                ]
            ]
        ];

        $newValuesCollection->add($textValue);
        $variantProduct->getValuesForVariation()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $variantProduct->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'my product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $textAttribute = new Attribute('name', 'text', [], false, false, null, null, false, 'text', []);
        $getAttributes->forCode('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->createByCheckingData($textAttribute, null, null, 'product')->willReturn($newTextValue);
        $newTextValue->getAttributeCode()->willReturn('text');
        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScopeCode()->willReturn(null);
        $newTextValue->getLocaleCode()->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($variantProduct, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new WriteValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $draftSource = new DraftSource('pim', 'PIM', 'mary', 'Mary Smith');

        $this->build($variantProduct, $draftSource)->shouldReturn($productDraft);
    }

    function it_builds_a_variant_product_draft_but_do_not_create_value_if_values_are_same_as_parent(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $getAttributes,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $variantProduct,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ValueInterface $colorValue,
        ComparatorInterface $textComparator,
        ComparatorInterface $colorComparator,
        EntityWithValuesDraftInterface $productDraft,
        WriteValueCollection $newValuesCollection,
        WriteValueCollection $originalValuesCollection,
        ProductModelInterface $parent
    ) {
        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getParent()->willReturn($parent);
        $parent->getRawValues()->willReturn([
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => 'purple'
                ]
            ]
        ]);
        $variantProduct->getParent()->willReturn(null);

        $rawValues = [
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my product'
                ]
            ],
            'color' => [
                '<all_channels>' => [
                    '<all_locales>' => 'purple'
                ]
            ]
        ];

        $newValuesCollection->add($textValue);
        $newValuesCollection->add($colorValue);
        $variantProduct->getValuesForVariation()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'product', 'locale' => null, 'scope' => null]
            ],
            'color' => [
                ['data' => 'blue', 'locale' => null, 'scope' => null]
            ]
        ]);

        $variantProduct->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'my product', 'locale' => null, 'scope' => null]
            ],
            'color' => [
                ['data' => 'blue', 'locale' => null, 'scope' => null]
            ]
        ]);

        $textAttribute = new Attribute('name', 'text', [], false, false, null, null, false, 'text', []);
        $getAttributes->forCode('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->createByCheckingData($textAttribute, null, null, 'product')->willReturn($newTextValue);
        $newTextValue->getAttributeCode()->willReturn('text');
        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScopeCode()->willReturn(null);
        $newTextValue->getLocaleCode()->willReturn(null);

        $colorAttribute = new Attribute('color', 'simpleselect', [], false, false, null, null, false, 'option', []);
        $getAttributes->forCode('color')->willReturn($colorAttribute);
        $comparatorRegistry->getAttributeComparator('simpleselect')->willReturn($colorComparator);
        $colorComparator->compare(
            ['data' => 'blue', 'locale' => null, 'scope' => null],
            ['data' => 'blue', 'locale' => null, 'scope' => null]
        )->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($variantProduct, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new WriteValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $draftSource = new DraftSource('pim', 'PIM', 'mary', 'Mary Smith');

        $this->build($variantProduct, $draftSource)->shouldReturn($productDraft);
    }
}

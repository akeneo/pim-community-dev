<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Comparator\ComparatorInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Factory\ValueFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Builder\EntityWithValuesDraftBuilder;
use PimEnterprise\Component\Workflow\Factory\ProductDraftFactory;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithValuesDraftBuilderSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $entityWithValuesDraftRepository,
        ValueCollectionFactoryInterface $valueCollectionFactory,
        ValueFactory $valueFactory
    ) {
        $this->beConstructedWith(
            $normalizer,
            $comparatorRegistry,
            $attributeRepository,
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
        $attributeRepository,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $product,
        AttributeInterface $textAttribute,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ComparatorInterface $textComparator,
        EntityWithValuesDraftInterface $productDraft,
        ValueCollectionInterface $newValuesCollection,
        ValueCollectionInterface $originalValuesCollection
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
        $product->getValues()->willReturn($newValuesCollection);
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

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getType()->willReturn('text');
        $textAttribute->isUnique()->willReturn(false);
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->create($textAttribute, null, null, 'product')->willReturn($newTextValue);
        $newTextValue->getAttribute()->willReturn($textAttribute);
        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScope()->willReturn(null);
        $newTextValue->getLocale()->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new ValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $this->build($product, 'mary')->shouldReturn($productDraft);
    }

    function it_builds_a_simple_product_draft_when_submitted_data_is_new(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $attributeRepository,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $product,
        AttributeInterface $textAttribute,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ComparatorInterface $textComparator,
        EntityWithValuesDraftInterface $productDraft,
        ValueCollectionInterface $newValuesCollection,
        ValueCollectionInterface $originalValuesCollection
    ) {
        $product->isVariant()->willReturn(false);
        $rawValues = [];

        $newValuesCollection->add($textValue);
        $product->getValues()->willReturn($newValuesCollection);
        $normalizer->normalize($newValuesCollection, 'standard')->willReturn([
            'name' => [
                ['data' => 'product', 'locale' => null, 'scope' => null]
            ]
        ]);

        $product->getRawValues()->willReturn($rawValues);
        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn($originalValuesCollection);
        $normalizer->normalize($originalValuesCollection, 'standard')->willReturn([]);

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getType()->willReturn('text');
        $textAttribute->isUnique()->willReturn(false);
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            []
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->create($textAttribute, null, null, 'product')->willReturn($newTextValue);
        $newTextValue->getAttribute()->willReturn($textAttribute);
        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScope()->willReturn(null);
        $newTextValue->getLocale()->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new ValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $this->build($product, 'mary')->shouldReturn($productDraft);
    }

    function it_does_not_build_a_simple_product_draft_if_submitted_data_is_the_same_as_product_data(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $textAttribute,
        ValueInterface $textValue,
        ComparatorInterface $textComparator,
        ValueCollectionInterface $newValuesCollection,
        ValueCollectionInterface $originalValuesCollection
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
        $product->getValues()->willReturn($newValuesCollection);
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

        $textAttribute->getType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'my product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(null);

        $this->build($product, 'mary')->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_attribute_does_not_exist(
        $normalizer,
        $valueCollectionFactory,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $textAttribute,
        ValueInterface $textValue,
        ValueCollectionInterface $newValuesCollection,
        ValueCollectionInterface $originalValuesCollection
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
        $product->getValues()->willReturn($newValuesCollection);
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

        $textAttribute->getCode()->willReturn('name');
        $attributeRepository->findOneByIdentifier('name')->willReturn(null);

        $this->shouldThrow(
            new \LogicException('Cannot find attribute with code "name".')
        )->during('build', [$product, 'mary']);
    }

    function it_builds_a_variant_product_draft_when_submitted_data_is_different_from_product_data(
        $normalizer,
        $valueCollectionFactory,
        $comparatorRegistry,
        $attributeRepository,
        $valueFactory,
        $entityWithValuesDraftRepository,
        ProductInterface $variantProduct,
        AttributeInterface $textAttribute,
        ValueInterface $textValue,
        ValueInterface $newTextValue,
        ComparatorInterface $textComparator,
        EntityWithValuesDraftInterface $productDraft,
        ValueCollectionInterface $newValuesCollection,
        ValueCollectionInterface $originalValuesCollection
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

        $textAttribute->getCode()->willReturn('name');
        $textAttribute->getType()->willReturn('text');
        $textAttribute->isUnique()->willReturn(false);
        $attributeRepository->findOneByIdentifier('name')->willReturn($textAttribute);
        $comparatorRegistry->getAttributeComparator('text')->willReturn($textComparator);
        $textComparator->compare(
            ['data' => 'product', 'locale' => null, 'scope' => null],
            ['data' => 'my product', 'locale' => null, 'scope' => null]
        )->willReturn(['data' => 'product', 'locale' => null, 'scope' => null]);

        $valueFactory->create($textAttribute, null, null, 'product')->willReturn($newTextValue);
        $newTextValue->getAttribute()->willReturn($textAttribute);
        $newTextValue->getData()->willReturn('product');
        $newTextValue->getScope()->willReturn(null);
        $newTextValue->getLocale()->willReturn(null);

        $entityWithValuesDraftRepository->findUserEntityWithValuesDraft($variantProduct, 'mary')->willReturn($productDraft);
        $productDraft->setValues(new ValueCollection([$newTextValue->getWrappedObject()]))->shouldBeCalled();
        $productDraft->setChanges([
            'values' => ['name' => [['data' => 'product', 'locale' => null, 'scope' => null]]]
        ])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT)->shouldBeCalled();

        $this->build($variantProduct, 'mary')->shouldReturn($productDraft);
    }
}

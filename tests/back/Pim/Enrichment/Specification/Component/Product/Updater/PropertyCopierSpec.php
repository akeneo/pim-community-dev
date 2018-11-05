<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyCopier;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\FieldCopierInterface;
use Prophecy\Argument;

class PropertyCopierSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CopierRegistryInterface $copierRegistry
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $copierRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PropertyCopier::class);
    }

    function it_copies_a_data_to_a_product_attribute(
        $copierRegistry,
        $attributeRepository,
        ProductInterface $product,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        AttributeCopierInterface $copier
    ) {
        $attributeRepository->findOneByIdentifier('color_one')->willReturn($fromAttribute);
        $attributeRepository->findOneByIdentifier('color_two')->willReturn($toAttribute);
        $copierRegistry->getCopier('color_one', 'color_two')->willReturn($copier);
        $copier
            ->copyAttributeData($product, $product, $fromAttribute, $toAttribute, [])
            ->shouldBeCalled();

        $this->copyData($product, $product, 'color_one', 'color_two');
    }

    function it_copies_a_data_to_a_product_field(
        $copierRegistry,
        $attributeRepository,
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        FieldCopierInterface $copier
    ) {
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $copierRegistry->getCopier('category', 'category')->willReturn($copier);
        $copier
            ->copyFieldData($fromProduct, $toProduct, 'category', 'category', [])
            ->shouldBeCalled();

        $this->copyData($fromProduct, $toProduct, 'category', 'category');
    }

    function it_copies_a_data_to_a_product_model_attribute(
        $copierRegistry,
        $attributeRepository,
        ProductModelInterface $productModel,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        AttributeCopierInterface $copier
    ) {
        $attributeRepository->findOneByIdentifier('color_one')->willReturn($fromAttribute);
        $attributeRepository->findOneByIdentifier('color_two')->willReturn($toAttribute);
        $copierRegistry->getCopier('color_one', 'color_two')->willReturn($copier);
        $copier
            ->copyAttributeData($productModel, $productModel, $fromAttribute, $toAttribute, [])
            ->shouldBeCalled();

        $this->copyData($productModel, $productModel, 'color_one', 'color_two');
    }

    function it_copies_a_data_to_a_product_model_field(
        $copierRegistry,
        $attributeRepository,
        ProductModelInterface $fromProductModel,
        ProductModelInterface $toProductModel,
        FieldCopierInterface $copier
    ) {
        $attributeRepository->findOneByIdentifier('category')->willReturn(null);
        $copierRegistry->getCopier('category', 'category')->willReturn($copier);
        $copier
            ->copyFieldData($fromProductModel, $toProductModel, 'category', 'category', [])
            ->shouldBeCalled();

        $this->copyData($fromProductModel, $toProductModel, 'category', 'category');
    }

    function it_throws_an_exception_when_it_copies_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('No copier found for fields "unknown_field" and "to_field"'))->during(
            'copyData', [$product, $product, 'unknown_field', 'to_field', []]
        );
    }

    function it_throws_an_exception_when_trying_to_copy_anything_else_than_a_product()
    {
        $this->shouldThrow(
            new InvalidObjectException(
                'stdClass',
                EntityWithValuesInterface::class,
                'Expects a "Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface", "stdClass" and "stdClass" provided.'
            )
        )->during(
            'copyData',
            [new \stdClass(), new \stdClass(), 'from_category', 'from_category', []]
        );
    }
}

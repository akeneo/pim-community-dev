<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;
use Prophecy\Argument;

class CopierActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyCopierInterface $propertyCopier,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($propertyCopier, $attributeRepository);
    }

    function it_supports_copy_action(ProductCopyActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_copy_action_on_non_variant_product(
        $propertyCopier,
        ProductCopyActionInterface $action,
        ProductInterface $product
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $propertyCopier->copyData(
            $product,
            $product,
            'sku',
            'name',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_copy_action_on_variant_product(
        $propertyCopier,
        $attributeRepository,
        ProductCopyActionInterface $action,
        VariantProductInterface $variantProduct,
        AttributeInterface $nameAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->getCode()->willReturn('name');

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(2);

        $variantProduct->getVariationLevel()->willReturn(2);

        $propertyCopier->copyData(
            $variantProduct,
            $variantProduct,
            'sku',
            'name',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$variantProduct]);
    }

    function it_applies_copy_action_on_product_model(
        $propertyCopier,
        $attributeRepository,
        ProductCopyActionInterface $action,
        ProductModelInterface $productModel,
        AttributeInterface $descriptionAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $action->getFromField()->willReturn('description');
        $action->getToField()->willReturn('description');
        $action->getOptions()->willReturn([
            'from_locale' => 'en_US',
            'to_locale' => 'en_US',
            'from_scope' => 'mobile',
            'to_scope' => 'tablet',
        ]);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->getCode()->willReturn('description');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('description')->willReturn(2);

        $productModel->getVariationLevel()->willReturn(2);

        $propertyCopier->copyData(
            $productModel,
            $productModel,
            'description',
            'description',
            [
                'from_locale' => 'en_US',
                'to_locale' => 'en_US',
                'from_scope' => 'mobile',
                'to_scope' => 'tablet',
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$productModel]);
    }

    function it_does_not_apply_copy_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        $propertyCopier,
        $attributeRepository,
        ProductCopyActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $nameAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->getCode()->willReturn('name');

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(2);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(1);

        $propertyCopier->copyData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_does_not_apply_copy_action_if_the_field_is_not_an_attribute(
        $propertyCopier,
        $attributeRepository,
        ProductCopyActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn(null);

        $propertyCopier->copyData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }
}

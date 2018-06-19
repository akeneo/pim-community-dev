<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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
        $attributeRepository,
        ProductCopyActionInterface $action,
        ProductInterface $product,
        AttributeInterface $nameAttribute,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->getCode()->willReturn('name');

        $product->getFamilyVariant()->willReturn(null);
        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

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
        ProductInterface $variantProduct,
        AttributeInterface $nameAttribute,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);

        $variantProduct->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

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
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
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

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('description')->willReturn(true);

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
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

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

    function it_does_not_apply_copy_action_if_the_field_is_not_an_attribute_of_the_family(
        $propertyCopier,
        $attributeRepository,
        ProductCopyActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $nameAttribute,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertyCopier->copyData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }
}

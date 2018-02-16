<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductAddActionInterface;
use Prophecy\Argument;

class AdderActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyAdderInterface $propertyAdder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($propertyAdder, $attributeRepository);
    }

    function it_applies_add_field_action_on_non_variant_product(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('color')->willReturn(null);
        $propertyAdder->addData($product, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_add_attribute_action_on_non_variant_product(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        AttributeInterface $colorAttribute,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);
        $product->getFamilyVariant()->willReturn(null);

        $propertyAdder->addData($product, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_add_action_on_variant_product(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        VariantProductInterface $variantProduct,
        AttributeInterface $colorAttribute,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);

        $variantProduct->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('color')->willReturn(2);

        $variantProduct->getVariationLevel()->willReturn(2);

        $propertyAdder->addData($variantProduct, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$variantProduct]);
    }

    function it_applies_add_action_on_product_model(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        ProductModelInterface $productModel,
        AttributeInterface $colorAttribute,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('color')->willReturn(2);

        $productModel->getVariationLevel()->willReturn(2);

        $propertyAdder->addData($productModel, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$productModel]);
    }

    function it_does_not_apply_add_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $colorAttribute,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('color')->willReturn(1);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

        $propertyAdder->addData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_applies_add_action_if_the_field_is_not_an_attribute(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ) {
        $action->getField()->willReturn('categories');
        $action->getItems()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('categories')->willReturn(null);

        $propertyAdder->addData($entityWithFamilyVariant, 'categories', ['socks'], [])->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_does_not_apply_add_action_if_the_field_is_not_an_attribute_of_the_family(
        $propertyAdder,
        $attributeRepository,
        ProductAddActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $colorAttribute,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('color')->willReturn($colorAttribute);

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertyAdder->addData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }
}

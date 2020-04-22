<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\VariantProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SetterActionApplierSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $propertySetter, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($propertySetter, $attributeRepository);
    }

    function it_supports_set_action(ProductSetActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_set_field_action_on_non_variant_product(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn(null);

        $propertySetter->setData(
            $product,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_set_attribute_action_on_non_variant_product(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        ProductInterface $product,
        AttributeInterface $name,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $product->getFamilyVariant()->willReturn(null);

        $propertySetter->setData(
            $product,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_set_action_on_variant_product(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        VariantProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $name,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $variantProduct->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $variantProduct->getVariationLevel()->willReturn(1);

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(1);

        $propertySetter->setData(
            $variantProduct,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$variantProduct]);
    }

    function it_applies_set_action_on_product_model(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $name,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $productModel->getVariationLevel()->willReturn(1);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(1);

        $propertySetter->setData(
            $productModel,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$productModel]);
    }

    function it_does_not_apply_set_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $name,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(1);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_applies_set_action_on_entity_with_family_variant_if_the_set_action_field_is_not_an_attribute(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entity
    ) {
        $action->getField()->willReturn('family');
        $action->getValue()->willReturn('socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('family')->willReturn(null);

        $propertySetter->setData(
            $entity,
            'family',
            'socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entity]);
    }

    function it_applies_set_action_on_entity_with_family_variant_on_categories_for_a_non_variant_product(
        $propertySetter,
        ProductSetActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('categories');
        $action->getValue()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $propertySetter->setData(
            $product,
            'categories',
            ['socks'],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_set_action_on_a_parentless_entity_categories(
        $propertySetter,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entity
    ) {
        $action->getField()->willReturn('categories');
        $action->getValue()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $entity->getParent()->willReturn(null);

        $propertySetter->setData(
            $entity,
            'categories',
            ['socks'],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entity]);
    }

    function it_applies_set_action_on_an_entity_if_it_includes_all_of_its_parent_categories_too(
        $propertySetter,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entity,
        ProductModelInterface $parent
    ) {
        $action->getField()->willReturn('categories');
        $action->getValue()->willReturn(['socks', 'clothing']);
        $action->getOptions()->willReturn([]);

        $entity->getParent()->willReturn($parent);
        $parent->getCategoryCodes()->willReturn(['socks']);

        $propertySetter->setData(
            $entity,
            'categories',
            ['socks', 'clothing'],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entity]);
    }

    function it_does_not_apply_set_action_on_an_entity_if_it_does_not_include_all_of_its_parent_categories_too(
        $propertySetter,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entity,
        ProductModelInterface $parent
    ) {
        $action->getField()->willReturn('categories');
        $action->getValue()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $entity->getParent()->willReturn($parent);
        $parent->getCategoryCodes()->willReturn(['socks', 'clothing']);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entity]);
    }

    function it_does_not_apply_set_action_if_the_field_is_not_an_attribute_of_the_family(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyInterface $family,
        AttributeInterface $name
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_sets_an_attribute_value_to_null_if_the_action_value_is_an_empty_string(
        PropertySetterInterface $propertySetter,
        AttributeRepositoryInterface $attributeRepository,
        ProductSetActionInterface $action,
        AttributeInterface $releaseDate,
        FamilyInterface $family
    ) {
        $action->getValue()->willReturn('');
        $action->getField()->willReturn('release_date');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('release_date')->willReturn($releaseDate);
        $family->getId()->willReturn(42);
        $family->hasAttributeCode('release_date')->willReturn(true);

        $product = (new Product())->setFamily($family->getWrappedObject());

        $propertySetter->setData($product, 'release_date', null, [])->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }
}

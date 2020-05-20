<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SetterActionApplierSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $propertySetter, GetAttributes $getAttributes, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($propertySetter, $getAttributes, $eventDispatcher);
    }

    function it_supports_set_action(ProductSetActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_set_field_action_on_non_variant_product(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        ProductSetActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn(null);

        $propertySetter->setData(
            $product,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_applies_set_attribute_action_on_non_variant_product(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        ProductSetActionInterface $action,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));
        $product->getFamilyVariant()->willReturn(null);

        $propertySetter->setData(
            $product,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_applies_set_action_on_variant_product(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        ProductSetActionInterface $action,
        ProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

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

        $this->applyAction($action, [$variantProduct])->shouldReturn([$variantProduct]);
    }

    function it_applies_set_action_on_product_model(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        ProductSetActionInterface $action,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

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

        $this->applyAction($action, [$productModel])->shouldReturn([$productModel]);
    }

    function it_does_not_apply_set_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(1);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([]);
    }

    function it_applies_set_action_on_entity_with_family_variant_if_the_set_action_field_is_not_an_attribute(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entity
    ) {
        $action->getField()->willReturn('family');
        $action->getValue()->willReturn('socks');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('family')->willReturn(null);

        $propertySetter->setData(
            $entity,
            'family',
            'socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entity])->shouldReturn([$entity]);
    }

    function it_applies_set_action_on_entity_with_family_variant_on_categories_for_a_non_variant_product(
        PropertySetterInterface $propertySetter,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $product
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

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_applies_set_action_on_a_parentless_entity_categories(
        PropertySetterInterface $propertySetter,
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

        $this->applyAction($action, [$entity])->shouldReturn([$entity]);
    }

    function it_applies_set_action_on_an_entity_if_it_includes_all_of_its_parent_categories_too(
        PropertySetterInterface $propertySetter,
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

        $this->applyAction($action, [$entity])->shouldReturn([$entity]);
    }

    function it_does_not_apply_set_action_on_an_entity_if_it_does_not_include_all_of_its_parent_categories_too(
        PropertySetterInterface $propertySetter,
        ProductSetActionInterface $action,
        EventDispatcherInterface $eventDispatcher,
        EntityWithFamilyVariantInterface $entity,
        ProductModelInterface $parent
    ) {
        $action->getField()->willReturn('categories');
        $action->getValue()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $entity->getParent()->willReturn($parent);
        $parent->getCategoryCodes()->willReturn(['socks', 'clothing']);

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($action, [$entity])->shouldReturn([]);
    }

    function it_does_not_apply_set_action_if_the_field_is_not_an_attribute_of_the_family(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([]);
    }

    function it_sets_an_attribute_value_to_null_if_the_action_value_is_an_empty_string(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $product,
        FamilyInterface $family
    ) {
        $action->getValue()->willReturn('');
        $action->getField()->willReturn('release_date');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('release_date')->willReturn($this->buildAttribute('release_date'));

        $family->getId()->willReturn(42);
        $family->hasAttributeCode('release_date')->willReturn(true);

        $product->getFamily()->willReturn($family);
        $product->getFamilyVariant()->willReturn(null);

        $propertySetter->setData($product, 'release_date', null, [])->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    private function buildAttribute(string $code): Attribute
    {
        return new Attribute(
            $code,
            'type',
            [],
            false,
            false,
            null,
            null,
            false,
            'backend_type',
            []
        );
    }
}

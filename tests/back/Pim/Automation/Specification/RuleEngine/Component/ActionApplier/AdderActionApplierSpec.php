<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AdderActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith($propertyAdder, $getAttributes);
    }

    function it_applies_add_field_action_on_non_variant_product(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('color')->willReturn(null);
        $propertyAdder->addData($product, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_applies_add_attribute_action_on_non_variant_product(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $getAttributes->forCode('color')->willReturn($this->buildAttribute('color'));
        $product->getFamilyVariant()->willReturn(null);

        $propertyAdder->addData($product, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_add_action_on_variant_product(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        ProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('color')->willReturn($this->buildAttribute('color'));

        $variantProduct->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('color')->willReturn(2);

        $variantProduct->getVariationLevel()->willReturn(2);

        $propertyAdder->addData($variantProduct, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$variantProduct])->shouldReturn([$variantProduct]);
    }

    function it_applies_add_action_on_product_model(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('color')->willReturn($this->buildAttribute('color'));

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('color')->willReturn(2);

        $productModel->getVariationLevel()->willReturn(2);

        $propertyAdder->addData($productModel, 'color', ['red', 'blue'], [])->shouldBeCalled();

        $this->applyAction($action, [$productModel])->shouldReturn([$productModel]);
    }

    function it_does_not_apply_add_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('color')->willReturn($this->buildAttribute('color'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(true);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('color')->willReturn(1);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

        $propertyAdder->addData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([]);
    }

    function it_applies_add_action_if_the_field_is_not_an_attribute(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ) {
        $action->getField()->willReturn('categories');
        $action->getItems()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('categories')->willReturn(null);
        $propertyAdder->addData($entityWithFamilyVariant, 'categories', ['socks'], [])->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([$entityWithFamilyVariant]);
    }

    function it_does_not_apply_add_action_if_the_field_is_not_an_attribute_of_the_family(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        ProductAddActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('color');
        $action->getItems()->willReturn(['red', 'blue']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('color')->willReturn($this->buildAttribute('color'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('color')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertyAdder->addData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([]);
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

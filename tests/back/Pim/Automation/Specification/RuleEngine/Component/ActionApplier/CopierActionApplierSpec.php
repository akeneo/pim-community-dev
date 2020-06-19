<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CopierActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($propertyCopier, $getAttributes, $eventDispatcher);
    }

    function it_supports_copy_action(ProductCopyActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_copy_action_on_non_variant_product(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        ProductCopyActionInterface $action,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

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

        $this->applyAction($action, [$product])->shouldReturn([$product]);
    }

    function it_applies_copy_action_on_variant_product(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        ProductCopyActionInterface $action,
        ProductInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

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

        $this->applyAction($action, [$variantProduct])->shouldReturn([$variantProduct]);
    }

    function it_applies_copy_action_on_product_model(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        ProductCopyActionInterface $action,
        ProductModelInterface $productModel,
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

        $getAttributes->forCode('description')->willReturn($this->buildAttribute('description'));

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

        $this->applyAction($action, [$productModel])->shouldReturn([$productModel]);
    }

    function it_does_not_apply_copy_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductCopyActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(true);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('name')->willReturn(2);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(1);

        $propertyCopier->copyData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([]);
    }

    function it_applies_copy_action_if_the_field_is_not_an_attribute(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        ProductCopyActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('categories');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('categories')->willReturn(null);

        $propertyCopier->copyData(
            $entityWithFamilyVariant,
            $entityWithFamilyVariant,
            'sku',
            'categories',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant])->shouldReturn([$entityWithFamilyVariant]);
    }

    function it_does_not_apply_copy_action_if_the_field_is_not_an_attribute_of_the_family(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher,
        ProductCopyActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyInterface $family
    ) {
        $action->getFromField()->willReturn('sku');
        $action->getToField()->willReturn('name');
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('name')->willReturn($this->buildAttribute('name'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('name')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertyCopier->copyData(Argument::cetera())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::type(SkippedActionForSubjectEvent::class))->shouldBeCalled();

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

<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductRemoveActionInterface;
use Prophecy\Argument;

class RemoverActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyRemoverInterface $propertyRemover,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($propertyRemover, $attributeRepository);
    }

    function it_supports_remove_action(ProductRemoveActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_remove_action_on_non_variant_product(
        $propertyRemover,
        ProductRemoveActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce'
        ]);
        $action->getItems()->willReturn([
            'multi1',
            'multi2'
        ]);

        $propertyRemover->removeData(
            $product,
            'multi-select',
            [
                'multi1',
                'multi2'
            ],
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce'
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_remove_action_on_variant_product(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        VariantProductInterface $variantProduct,
        AttributeInterface $multiSelectAttribute,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(2);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($multiSelectAttribute);
        $attributeIterator->rewind()->shouldBeCalled();

        $variantProduct->getVariationLevel()->willReturn(2);

        $propertyRemover->removeData(
            $variantProduct,
            'multi-select',
            [
                'multi1',
                'multi2'
            ],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$variantProduct]);
    }

    function it_applies_remove_action_on_product_model(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        ProductModelInterface $productModel,
        AttributeInterface $multiSelectAttribute,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(2);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($multiSelectAttribute);
        $attributeIterator->rewind()->shouldBeCalled();

        $productModel->getVariationLevel()->willReturn(2);

        $propertyRemover->removeData(
            $productModel,
            'multi-select',
            [
                'multi1',
                'multi2'
            ],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$productModel]);
    }

    function it_applies_remove_action_on_entity_with_family_variant_if_attribute_is_a_common_one(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $multiSelectAttribute,
        AttributeInterface $anotherMultiSelectAttribute,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($anotherMultiSelectAttribute);
        $attributeIterator->rewind()->shouldBeCalled();
        $attributeIterator->next()->shouldBeCalled();

        $anotherMultiSelectAttribute->getCode()->willReturn('anoter_multi_select');
        $entityWithFamilyVariant->getVariationLevel()->willReturn(0);

        $propertyRemover->removeData(
            $entityWithFamilyVariant,
            'multi-select',
            [
                'multi1',
                'multi2'
            ],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_does_not_apply_remove_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $multiSelectAttribute,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(1);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($multiSelectAttribute);
        $attributeIterator->rewind()->shouldBeCalled();

        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

        $propertyRemover->removeData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_does_not_apply_remove_action_on_entity_with_family_variant_if_it_does_not_have_the_attribute(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $multiSelectAttribute,
        AttributeInterface $anotherMultiSelectAttribute,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($anotherMultiSelectAttribute);
        $attributeIterator->rewind()->shouldBeCalled();
        $attributeIterator->next()->shouldBeCalled();

        $anotherMultiSelectAttribute->getCode()->willReturn('anoter_multi_select');
        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

        $propertyRemover->removeData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_applies_remove_action_if_the_field_is_not_an_attribute(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ) {
        $action->getField()->willReturn('categories');
        $action->getItems()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('categories')->willReturn(null);

        $propertyRemover->removeData($entityWithFamilyVariant, 'categories', ['socks'], [])->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }
}

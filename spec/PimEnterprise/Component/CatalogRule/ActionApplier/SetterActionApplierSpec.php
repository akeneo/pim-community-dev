<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
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
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;
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

    function it_applies_set_action_on_non_variant_product(
        $propertySetter,
        ProductSetActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);


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
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator,
        AttributeInterface $name
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $variantProduct->getVariationLevel()->willReturn(1);

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(1);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($name);
        $attributeIterator->rewind()->shouldBeCalled();

        $name->getCode()->willReturn('name');

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
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator,
        AttributeInterface $name
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $productModel->getVariationLevel()->willReturn(1);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(1);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($name);
        $attributeIterator->rewind()->shouldBeCalled();

        $name->getCode()->willReturn('name');

        $propertySetter->setData(
            $productModel,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$productModel]);
    }

    function it_applies_set_action_on_entity_with_family_variant_if_attribute_is_a_common_one(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator,
        AttributeInterface $name
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(0);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(0);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($name);
        $attributeIterator->rewind()->shouldBeCalled();
        $attributeIterator->next()->shouldBeCalled();

        $name->getCode()->willReturn('another_name');

        $propertySetter->setData(
            $entityWithFamilyVariant,
            'name',
            'sexy socks',
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_does_not_apply_set_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator,
        AttributeInterface $name
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(2);

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
        $attributeIterator->current()->willReturn($name);
        $attributeIterator->rewind()->shouldBeCalled();

        $name->getCode()->willReturn('name');

        $propertySetter->setData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_does_not_apply_set_action_on_entity_with_family_variant_if_it_does_not_have_the_attribute(
        $propertySetter,
        $attributeRepository,
        ProductSetActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        Collection $attributeSetCollection,
        \Iterator $attributeSetsIterator,
        VariantAttributeSetInterface $attributeSet,
        Collection $attributeCollection,
        \Iterator $attributeIterator,
        AttributeInterface $name
    ) {
        $action->getField()->willReturn('name');
        $action->getValue()->willReturn('sexy socks');
        $action->getOptions()->willReturn([]);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(1);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getVariantAttributeSets()->willReturn($attributeSetCollection);

        $attributeSetCollection->getIterator()->willReturn($attributeSetsIterator);
        $attributeSetsIterator->valid()->willReturn(true, false);
        $attributeSetsIterator->current()->willReturn($attributeSet);
        $attributeSetsIterator->rewind()->shouldBeCalled();
        $attributeSetsIterator->next()->shouldBeCalled();

        $attributeSet->getLevel()->willReturn(1);
        $attributeSet->getAttributes()->willReturn($attributeCollection);

        $attributeCollection->getIterator()->willReturn($attributeIterator);
        $attributeIterator->valid()->willReturn(true, false);
        $attributeIterator->current()->willReturn($name);
        $attributeIterator->rewind()->shouldBeCalled();
        $attributeIterator->next()->shouldBeCalled();

        $name->getCode()->willReturn('another_name');

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
}

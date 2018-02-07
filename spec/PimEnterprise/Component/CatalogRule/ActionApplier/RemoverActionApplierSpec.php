<?php

namespace spec\PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductRemoveActionInterface;
use Prophecy\Argument;

class RemoverActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyRemoverInterface $propertyRemover,
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($propertyRemover, $attributeRepository, $categoryRepository);
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
        FamilyVariantInterface $familyVariant
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('multi-select')->willReturn(2);

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
        FamilyVariantInterface $familyVariant
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('multi-select')->willReturn(2);

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

    function it_does_not_apply_remove_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        $propertyRemover,
        $attributeRepository,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        AttributeInterface $multiSelectAttribute,
        FamilyVariantInterface $familyVariant
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $attributeRepository->findOneByIdentifier('multi-select')->willReturn($multiSelectAttribute);
        $multiSelectAttribute->getCode()->willReturn('multi-select');

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('multi-select')->willReturn(2);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(1);

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

    function it_removes_children_categories_with_include_children_option_set_to_true(
        $propertyRemover,
        $categoryRepository,
        ProductRemoveActionInterface $action,
        EntityWithValuesInterface $entityWithValues,
        CategoryInterface $firstCategory,
        CategoryInterface $secondCategory
    ) {
        $action->getItems()->willReturn(
            [
                'first_category',
                'second_category',
            ]
        );
        $action->getOptions()->willReturn(
            [
                'locale'           => null,
                'scope'            => null,
                'include_children' => true,
            ]
        );
        $action->getField()->willReturn('categories');

        $categoryRepository->getCategoriesByCodes(['first_category', 'second_category'])
                           ->willReturn([$firstCategory, $secondCategory]);
        $categoryRepository->getAllChildrenCodes($firstCategory)->willReturn(['first_category_child']);
        $categoryRepository->getAllChildrenCodes($secondCategory)->willReturn(
            [
                'second_category_child',
                'second_category_other_child',
            ]
        );

        $propertyRemover->removeData(
            $entityWithValues,
            'categories',
            [
                'first_category',
                'second_category',
                'first_category_child',
                'second_category_child',
                'second_category_other_child',
            ],
            [
                'locale'           => null,
                'scope'            => null,
                'include_children' => true,
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$entityWithValues]);
    }
}

<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\RemoverActionApplier;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductRemoveActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyRemoverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoverActionApplierSpec extends ObjectBehavior
{
    function let(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($propertyRemover, $getAttributes, $categoryRepository);
    }

    function it_supports_remove_action(ProductRemoveActionInterface $action)
    {
        $this->supports($action)->shouldReturn(true);
    }

    function it_applies_remove_attribute_action_on_non_variant_product(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
        ]);
        $action->getItems()->willReturn([
            'multi1',
            'multi2',
        ]);

        $product->getFamily()->willReturn($family);
        $family->hasAttributeCode('multi-select')->willReturn(true);
        $product->getFamilyVariant()->willReturn(null);
        $getAttributes->forCode('multi-select')->willReturn($this->buildAttribute('multi-select'));

        $propertyRemover->removeData(
            $product,
            'multi-select',
            [
                'multi1',
                'multi2',
            ],
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
            ]
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_remove_field_action_on_non_variant_product(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        ProductInterface $product
    ) {
        $action->getField()->willReturn('category');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn([
            'foo',
            'bar',
        ]);

        $getAttributes->forCode('category')->willReturn(null);
        $product->getFamilyVariant()->willReturn(null);

        $propertyRemover->removeData(
            $product,
            'category',
            [
                'foo',
                'bar',
            ],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$product]);
    }

    function it_applies_remove_action_on_variant_product(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $variantProduct,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $getAttributes->forCode('multi-select')->willReturn($this->buildAttribute('multi-select'));

        $variantProduct->getFamily()->willReturn($family);
        $family->hasAttributeCode('multi-select')->willReturn(true);

        $variantProduct->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('multi-select')->willReturn(2);

        $variantProduct->getVariationLevel()->willReturn(2);

        $propertyRemover->removeData(
            $variantProduct,
            'multi-select',
            [
                'multi1',
                'multi2',
            ],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$variantProduct]);
    }

    function it_applies_remove_action_on_product_model(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $getAttributes->forCode('multi-select')->willReturn($this->buildAttribute('multi-select'));

        $productModel->getFamily()->willReturn($family);
        $family->hasAttributeCode('multi-select')->willReturn(true);

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('multi-select')->willReturn(2);

        $productModel->getVariationLevel()->willReturn(2);

        $propertyRemover->removeData(
            $productModel,
            'multi-select',
            [
                'multi1',
                'multi2',
            ],
            []
        )->shouldBeCalled();

        $this->applyAction($action, [$productModel]);
    }

    function it_does_not_apply_remove_action_on_entity_with_family_variant_if_variation_level_is_not_right(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $getAttributes->forCode('multi-select')->willReturn($this->buildAttribute('multi-select'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('multi-select')->willReturn(true);

        $entityWithFamilyVariant->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getLevelForAttributeCode('multi-select')->willReturn(2);

        $entityWithFamilyVariant->getVariationLevel()->willReturn(1);

        $propertyRemover->removeData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_applies_remove_action_if_the_field_is_not_an_attribute(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant
    ) {
        $action->getField()->willReturn('categories');
        $action->getItems()->willReturn(['socks']);
        $action->getOptions()->willReturn([]);

        $getAttributes->forCode('categories')->willReturn(null);

        $propertyRemover->removeData($entityWithFamilyVariant, 'categories', ['socks'], [])->shouldBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
    }

    function it_removes_children_categories_with_include_children_option_set_to_true(
        PropertyRemoverInterface $propertyRemover,
        CategoryRepositoryInterface $categoryRepository,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithValues,
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

    function it_throws_exception_if_items_is_not_an_array(
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithValues
    ) {
        $action->getField()->willReturn('foo');
        $action->getItems()->willReturn('Not an array');

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'foo',
                RemoverActionApplier::class,
                'Not an array'
            )
        )->during('applyAction', [$action, [$entityWithValues]]);
    }

    function it_does_not_apply_remove_action_if_the_field_is_not_an_attribute_of_the_family(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        ProductRemoveActionInterface $action,
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        FamilyInterface $family
    ) {
        $action->getField()->willReturn('multi-select');
        $action->getOptions()->willReturn([]);
        $action->getItems()->willReturn(['multi1', 'multi2']);

        $getAttributes->forCode('multi-select')->willReturn($this->buildAttribute('multi-select'));

        $entityWithFamilyVariant->getFamily()->willReturn($family);
        $family->hasAttributeCode('multi-select')->willReturn(false);

        $entityWithFamilyVariant->getFamilyVariant()->shouldNotBeCalled();
        $propertyRemover->removeData(Argument::cetera())->shouldNotBeCalled();

        $this->applyAction($action, [$entityWithFamilyVariant]);
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

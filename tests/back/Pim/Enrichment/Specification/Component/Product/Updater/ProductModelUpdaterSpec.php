<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Association\ParentAssociationsFilter;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;

class ProductModelUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ParentAssociationsFilter $parentAssociationsFilter
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $valuesUpdater,
            $familyVariantRepository,
            $productModelRepository,
            $parentAssociationsFilter,
            ['categories'],
            ['code']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_product_model(
        $familyVariantRepository,
        $productModelRepository,
        $propertySetter,
        $valuesUpdater,
        ProductModelInterface $productModel,
        ProductModelInterface $parentProductModel,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getId()->willReturn(null);
        $productModel->getParent()->willReturn(null);
        $productModel->getFamilyVariant()->willReturn(null);

        $propertySetter->setData($productModel, 'categories', ['tshirt'])->shouldBeCalled();
        $productModel->setCode('product_model_code')->shouldBeCalled();
        $productModelRepository->findOneByIdentifier('product_model_parent')->willreturn($parentProductModel);
        $productModel->setParent($parentProductModel)->shouldBeCalled();

        $familyVariantRepository->findOneByIdentifier('clothing_color_size')->willreturn($familyVariant);
        $productModel->setFamilyVariant($familyVariant)->shouldBeCalled();
        $parentProductModel->isRootProductModel()->willReturn(true);

        $valuesUpdater->update($productModel, [
            'name' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt',
            ],
            'description' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt super beau',
            ],
        ], [])->shouldBeCalled();

        $this->update($productModel, [
            'code' => 'product_model_code',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
            'family_variant' => 'clothing_color_size',
            'parent' => 'product_model_parent'
        ])->shouldReturn($this);
    }

    function it_throws_an_exception_if_a_parent_is_set_to_a_root_product_model(ProductModelInterface $productModel)
    {
        $productModel->getId()->willReturn(42);
        $productModel->isRootProductModel()->willReturn(true);

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'parent' => 'parent'
        ]]);
    }

    function it_throws_an_exception_if_the_new_parent_is_not_a_root_product_model(
        ProductModelInterface $productModel,
        ProductModelInterface $currentParent,
        ProductModelInterface $newParent,
        $productModelRepository,
        FamilyVariantInterface $familyVariant,
        FamilyVariantInterface $familyVariant2
    )
    {
        $productModel->getId()->willReturn(42);
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($currentParent);
        $productModel->getFamilyVariant()->willReturn($familyVariant);

        $productModel->getParent()->willReturn($currentParent);

        $currentParent->getCode()->willReturn('parent');
        $currentParent->getFamilyVariant()->willReturn($familyVariant);

        $newParent->getFamilyVariant()->willReturn($familyVariant2);
        $newParent->isRootProductModel()->willReturn(false);

        $productModelRepository->findOneByIdentifier('new_parent')->willreturn($newParent);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'parent' => 'new_parent',
        ]]);
    }

    function it_throws_an_exception_if_a_non_existing_parent_is_set_to_a_product_model(
        $productModelRepository,
        ProductModelInterface $productModel
    ) {
        $productModel->getId()->willReturn(null);
        $productModelRepository->findOneByIdentifier('wrong_code')->willreturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'parent' => 'wrong_code'
        ]]);
    }

    function it_throws_an_exception_when_giving_a_new_parent_with_a_different_family_variant(
        ProductModelInterface $productModel,
        ProductModelInterface $currentParent,
        ProductModelInterface $newParent,
        $productModelRepository,
        FamilyVariantInterface $familyVariant,
        FamilyVariantInterface $familyVariant2
    ) {

        $productModel->getId()->willReturn(42);
        $productModel->isRootProductModel()->willReturn(false);
        $productModel->getParent()->willReturn($currentParent);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willReturn('current_family_variant');

        $productModel->getParent()->willReturn($currentParent);

        $currentParent->getCode()->willReturn('parent');
        $currentParent->getFamilyVariant()->willReturn($familyVariant);

        $newParent->getFamilyVariant()->willReturn($familyVariant2);
        $familyVariant2->getCode()->willReturn('new_family_variant');
        $newParent->isRootProductModel()->willReturn(true);

        $productModelRepository->findOneByIdentifier('new_parent')->willreturn($newParent);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'parent' => 'new_parent',
        ]]);
    }

    function it_throws_an_exception_if_the_family_variant_code_is_invalid(
        $familyVariantRepository,
        ProductModelInterface $productModel
    ) {
        $familyVariantRepository->findOneByIdentifier('wrong_code')->willreturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'wrong_code'
        ]]);
    }

    function it_throws_an_exception_if_the_family_variant_is_updated(
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willreturn('family_variant');

        $this->shouldThrow(ImmutablePropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'new_family_variant'
        ]]);
    }

    function it_throws_an_exception_if_the_family_variant_is_different_from_the_parent(
        ProductModelInterface $productModel,
        ProductModelInterface $parent,
        FamilyVariantInterface $familyVariant
    ) {
        $productModel->getFamilyVariant()->willReturn(null);
        $productModel->getParent()->willReturn($parent);
        $parent->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willreturn('family_variant');

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'new_family_variant'
        ]]);
    }

    function it_only_works_with_product_model(ProductInterface $product)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$product, [], []]);
    }

    function it_throws_an_exception_when_giving_a_non_scalar_code(
        ProductModelInterface $productModel
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$productModel, ['code' => []]]);
    }

    function it_throws_an_exception_when_giving_a_non_scalar_family_variant(
        ProductModelInterface $productModel
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$productModel, ['family_variant' => []]]);
    }

    function it_throws_an_exception_when_giving_non_scalar_categories(
        ProductModelInterface $productModel
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$productModel, ['categories' => '']]);
    }

    function it_throws_an_exception_when_giving_an_array_of_categories_with_non_scalar_values(
        ProductModelInterface $productModel
    ) {
        $this->shouldThrow(
            InvalidPropertyTypeException::class
        )->during('update', [$productModel, ['categories' => [[]]]]);
    }

    function it_throws_an_exception_when_giving_an_unknown_property(
        ProductModelInterface $productModel
    ) {
        $this->shouldThrow(
            UnknownPropertyException::class
        )->during('update', [$productModel, ['michel' => [[]]]]);
    }
}

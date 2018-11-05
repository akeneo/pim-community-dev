<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\CategoryFieldRemover;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class CategoryFieldRemoverSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository, ['pim_catalog_category']);
    }

    function it_is_a_field_remover()
    {
        $this->shouldImplement(FieldRemoverInterface::class);
        $this->shouldImplement(RemoverInterface::class);
    }

    function it_removes_categories_to_a_product(
        $categoryRepository,
        CategoryInterface $bookCategory,
        CategoryInterface $penCategory,
        CategoryInterface $bluePenCategory,
        ProductInterface $bookProduct,
        ProductInterface $bluePenProduct
    ) {
        $categoryRepository->findOneByIdentifier('book_category')->willReturn($bookCategory);
        $bookProduct->removeCategory($bookCategory)->shouldBeCalled();

        $categoryRepository->findOneByIdentifier('pen_category')->willReturn($penCategory);
        $categoryRepository->findOneByIdentifier('blue_pen_category')->willReturn($bluePenCategory);
        $bluePenProduct->removeCategory($penCategory)->shouldBeCalled();
        $bluePenProduct->removeCategory($bluePenCategory)->shouldBeCalled();

        $this->removeFieldData($bookProduct, 'categories', ['book_category']);
        $this->removeFieldData($bluePenProduct, 'categories', ['pen_category', 'blue_pen_category']);
    }

    function it_throws_an_exception_if_category_to_remove_is_not_found(
        $categoryRepository,
        ProductInterface $bookProduct
    ) {
        $categoryRepository->findOneByIdentifier('unknown_category')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'categories',
                'category code',
                'The category does not exist',
                CategoryFieldRemover::class,
                'unknown_category'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', ['unknown_category']);
    }

    function it_throws_an_exception_if_data_are_invalid(ProductInterface $bookProduct)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'categories',
                CategoryFieldRemover::class,
                'category_code'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', 'category_code');

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'categories',
                'one of the category codes is not a string, "integer" given',
                CategoryFieldRemover::class,
                [42]
            )
        )->duringRemoveFieldData($bookProduct, 'categories', [42]);
    }
}

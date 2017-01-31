<?php

namespace spec\Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class CategoryFieldRemoverSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository, ['pim_catalog_category']);
    }

    function it_is_a_field_remover()
    {
        $this->shouldImplement('\Pim\Component\Catalog\Updater\Remover\FieldRemoverInterface');
        $this->shouldImplement('\Pim\Component\Catalog\Updater\Remover\RemoverInterface');
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
            InvalidArgumentException::expected(
                'categories',
                'existing category code',
                'Pim\Component\Catalog\Updater\Remover\CategoryFieldRemover',
                'unknown_category'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', ['unknown_category']);
    }

    function it_throws_an_exception_if_data_are_invalid(ProductInterface $bookProduct)
    {
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected(
                'categories',
                'Pim\Component\Catalog\Updater\Remover\CategoryFieldRemover',
                'string'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', 'category_code');

        $this->shouldThrow(
            InvalidArgumentException::arrayStringValueExpected(
                'categories',
                0,
                'Pim\Component\Catalog\Updater\Remover\CategoryFieldRemover',
                'integer'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', [42]);
    }
}

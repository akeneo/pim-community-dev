<?php

namespace spec\Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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
            new InvalidArgumentException(
                'Attribute or field "categories" expects existing category code as data, "unknown_category" given (for remover category).'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', ['unknown_category']);
    }

    function it_throws_an_exception_if_data_are_invalid(ProductInterface $bookProduct)
    {
        $this->shouldThrow(
            new InvalidArgumentException(
                'Attribute or field "categories" expects an array as data, "string" given (for remover category).'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', 'category_code');

        $this->shouldThrow(
            new InvalidArgumentException(
                'Attribute or field "categories" expects an array with a string value for the key "0", "integer" given (for remover category).'
            )
        )->duringRemoveFieldData($bookProduct, 'categories', [42]);
    }
}

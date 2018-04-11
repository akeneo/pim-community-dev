<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\Counter;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

class CategoryProductsCounterSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($pqbFactory, $categoryRepository);
    }

    function it_gets_items_count_in_category_without_children(
        $categoryRepository,
        CategoryInterface $category
    ) {

        $categoryRepository->countProducts($category)->willReturn(114);
        $categoryRepository->countProductsWithChildren($category)->shouldNotBeCalled();

        $this->getItemsCountInCategory($category, false, true)->shouldReturn(114);
    }

    function it_gets_items_count_in_category_with_children(
        $categoryRepository,
        CategoryInterface $category
    ) {
        $categoryRepository->countProducts($category)->shouldNotBeCalled();
        $categoryRepository->countProductsWithChildren($category)->willReturn(1220);

        $this->getItemsCountInCategory($category, true, true)->shouldReturn(1220);
    }
}

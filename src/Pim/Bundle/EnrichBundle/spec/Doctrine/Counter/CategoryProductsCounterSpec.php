<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\Counter;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

class CategoryProductsCounterSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($pqbFactory, $categoryRepository);
    }

    function it_gets_items_count_in_category_without_children(
        $pqbFactory,
        $categoryRepository,
        CategoryInterface $category,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor
    ) {
        $category->getCode()->willReturn('short');
        $categoryRepository->getAllChildrenCodes($category, true)->shouldNotBeCalled();

        $pqbFactory->create([
            'filters' => [
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value' => ['short']
                ]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->count()->willReturn(114);

        $this->getItemsCountInCategory($category, false, true)->shouldReturn(114);
    }

    function it_gets_items_count_in_category_with_children(
        $pqbFactory,
        $categoryRepository,
        CategoryInterface $category,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor
    ) {
        $category->getCode()->willReturn('short');
        $categoryRepository->getAllChildrenCodes($category, true)->willReturn([
            'short', 'short_children', 'short_adults'
        ]);

        $pqbFactory->create([
            'filters' => [
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value' => ['short', 'short_children', 'short_adults']
                ]
            ]
        ])->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->count()->willReturn(1220);

        $this->getItemsCountInCategory($category, true, true)->shouldReturn(1220);
    }
}

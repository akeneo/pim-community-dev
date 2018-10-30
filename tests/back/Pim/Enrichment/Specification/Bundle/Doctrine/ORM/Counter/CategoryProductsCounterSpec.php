<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

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

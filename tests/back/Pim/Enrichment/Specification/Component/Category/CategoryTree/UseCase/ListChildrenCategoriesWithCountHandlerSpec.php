<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCountHandler;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;

class ListChildrenCategoriesWithCountHandlerSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        Query\ListChildrenCategoriesWithCountIncludingSubCategories $listIncludingSubCategories,
        Query\ListChildrenCategoriesWithCountNotIncludingSubCategories $listNotIncludingSubCategories
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $userContext,
            $listIncludingSubCategories,
            $listNotIncludingSubCategories
        );
    }

    function it_is_an_handler()
    {
        $this->shouldHaveType(ListChildrenCategoriesWithCountHandler::class);
    }

    function it_handles_children_categories_with_count_including_sub_categories(
        $categoryRepository,
        $listIncludingSubCategories,
        CategoryInterface $categoryToExpand
    ) {
        $categoryRepository->find(1)->willReturn($categoryToExpand);
        $categoryToExpand->getId()->willReturn(1);

        $listIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);

        $query = new ListChildrenCategoriesWithCount(1, -1, true, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);
    }

    function it_handles_children_categories_with_count_not_including_sub_categories(
        $categoryRepository,
        $listNotIncludingSubCategories,
        CategoryInterface $categoryToExpand
    ) {
        $categoryRepository->find(1)->willReturn($categoryToExpand);
        $categoryToExpand->getId()->willReturn(1);

        $listNotIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);

        $query = new ListChildrenCategoriesWithCount(1, -1, false, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);
    }

    function it_handles_children_categories_of_user_product_category_tree_when_no_category_selected_as_filter(
        $userContext,
        $listNotIncludingSubCategories,
        CategoryInterface $treeToExpand
    ) {
        $userContext->getUserProductCategoryTree()->willReturn($treeToExpand);
        $treeToExpand->getId()->willReturn(1);

        $listNotIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);

        $query = new ListChildrenCategoriesWithCount(-1, -1, false, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);
    }

    function it_handles_children_categories_with_category_selected_as_filter(
        $categoryRepository,
        $listNotIncludingSubCategories,
        CategoryInterface $categoryToExpand,
        CategoryInterface $categoryToFilterWith
    ) {
        $categoryRepository->find(1)->willReturn($categoryToExpand);
        $categoryToExpand->getId()->willReturn(1);

        $categoryRepository->isAncestor($categoryToExpand, $categoryToFilterWith)->willReturn(true);
        $categoryRepository->find(3)->willReturn($categoryToFilterWith);
        $categoryToFilterWith->getId()->willReturn(3);

        $listNotIncludingSubCategories->list('en_US', 1, 1, 3)->willReturn([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);

        $query = new ListChildrenCategoriesWithCount(1, 3, false, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new ChildCategory(1, 'code', 'label', true, true, 10, [])
        ]);
    }
}

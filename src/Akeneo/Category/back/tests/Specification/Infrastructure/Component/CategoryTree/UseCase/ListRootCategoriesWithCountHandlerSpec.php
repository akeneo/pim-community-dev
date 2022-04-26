<?php

namespace Specification\Akeneo\Category\Infrastructure\Component\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\CategoryTree\Query;
use Akeneo\Category\Infrastructure\Component\CategoryTree\ReadModel\RootCategory;
use Akeneo\Category\Infrastructure\Component\CategoryTree\UseCase\ListRootCategoriesWithCount;
use Akeneo\Category\Infrastructure\Component\CategoryTree\UseCase\ListRootCategoriesWithCountHandler;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;

class ListRootCategoriesWithCountHandlerSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface                                $categoryRepository,
        UserContext                                                $userContext,
        Query\ListRootCategoriesWithCountIncludingSubCategories    $listIncludingSubCategories,
        Query\ListRootCategoriesWithCountNotIncludingSubCategories $listNotIncludingSubCategories
    )
    {
        $this->beConstructedWith(
            $categoryRepository,
            $userContext,
            $listIncludingSubCategories,
            $listNotIncludingSubCategories
        );
    }

    function it_is_an_handler()
    {
        $this->shouldHaveType(ListRootCategoriesWithCountHandler::class);
    }

    function it_handles_root_categories_with_count_including_sub_categories(
        $categoryRepository,
        $listIncludingSubCategories,
        CategoryInterface $categoryToFilterWith
    )
    {
        $categoryRepository->find(2)->willReturn($categoryToFilterWith);
        $categoryToFilterWith->getRoot()->willReturn(1);

        $listIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);

        $query = new ListRootCategoriesWithCount(2, true, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);
    }

    function it_handles_root_categories_with_count_not_including_sub_categories(
        $categoryRepository,
        $listNotIncludingSubCategories,
        CategoryInterface $categoryToFilterWith
    )
    {
        $categoryRepository->find(2)->willReturn($categoryToFilterWith);
        $categoryToFilterWith->getRoot()->willReturn(1);

        $listNotIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);

        $query = new ListRootCategoriesWithCount(2, false, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);
    }

    function it_handles_root_categories_by_selecting_user_product_category_tree_when_no_category_selected_as_filter(
        $userContext,
        $listNotIncludingSubCategories,
        CategoryInterface $treeToExpand
    )
    {
        $userContext->getAccessibleUserTree()->willReturn($treeToExpand);
        $treeToExpand->getRoot()->willReturn(1);

        $listNotIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);

        $query = new ListRootCategoriesWithCount(-1, false, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);
    }

    function it_handles_query_for_users_that_have_no_access_to_any_category($userContext)
    {
        $userContext->getAccessibleUserTree()->willReturn(null);

        $query = new ListRootCategoriesWithCount(-1, false, 1, 'en_US');
        $this->handle($query)->shouldBeLike([]);
    }
}

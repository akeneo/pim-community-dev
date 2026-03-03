<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCountHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;

class ListRootCategoriesWithCountHandlerSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        ListRootCategoriesWithCountIncludingSubCategories $listIncludingSubCategories,
        ListRootCategoriesWithCountNotIncludingSubCategories $listNotIncludingSubCategories
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
        $this->shouldHaveType(ListRootCategoriesWithCountHandler::class);
    }

    function it_handles_root_categories_with_count_including_sub_categories(
        $userContext,
        $categoryRepository,
        $listIncludingSubCategories,
        CategoryInterface $categoryToFilterWith,
        CategoryInterface $categoryTreeToFilterWith,
    ) {
        $userContext->getAccessibleUserTree()->willReturn($categoryTreeToFilterWith);
        $categoryTreeToFilterWith->getId()->willReturn(1);
        $categoryRepository->find(2)->willReturn($categoryToFilterWith);
        $categoryToFilterWith->getRoot()->willReturn(1);
        $categoryRepository->find(1)->willReturn($categoryTreeToFilterWith);

        $listIncludingSubCategories->list('en_US', 1, 1, null)->willReturn([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);

        $query = new ListRootCategoriesWithCount(2, true, 1, 'en_US');
        $this->handle($query)->shouldBeLike([
            new RootCategory(1, 'code', 'label', 10, true)
        ]);
    }

    function it_handles_root_categories_with_count_not_including_sub_categories(
        $userContext,
        $categoryRepository,
        $listNotIncludingSubCategories,
        CategoryInterface $categoryToFilterWith,
        CategoryInterface $categoryTreeToFilterWith
    ) {
        $userContext->getAccessibleUserTree()->willReturn($categoryTreeToFilterWith);
        $categoryTreeToFilterWith->getId()->willReturn(1);
        $categoryRepository->find(2)->willReturn($categoryToFilterWith);
        $categoryToFilterWith->getRoot()->willReturn(1);
        $categoryRepository->find(1)->willReturn($categoryTreeToFilterWith);

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
        $categoryRepository,
        $listNotIncludingSubCategories,
        CategoryInterface $treeToExpand
    ) {
        $userContext->getAccessibleUserTree()->willReturn($treeToExpand);
        $treeToExpand->getId()->willReturn(1);
        $categoryRepository->find(-1)->willReturn(null);
        $categoryRepository->find(1)->willReturn($treeToExpand);
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

    function it_handles_categories_when_selected_tree_id_is_given_and_selected_filter_is_all_products(
        $userContext,
        $categoryRepository,
        $listNotIncludingSubCategories,
        CategoryInterface $categoryTreeToFilterWith,
        CategoryInterface $userDefautlCategoryTree,
    ) {
        $userContext->getAccessibleUserTree()->willReturn($userDefautlCategoryTree);
        $userDefautlCategoryTree->getId()->willReturn(7);
        $categoryRepository->find(-2)->willReturn(null);
        $categoryRepository->find(42)->willReturn($categoryTreeToFilterWith);
        $categoryTreeToFilterWith->getRoot()->willReturn(42);
        $categoryTreeToFilterWith->getId()->willReturn(42);

        $listNotIncludingSubCategories->list('en_US', 1, 42, null)->willReturn([
            new RootCategory(42, 'code_42', 'label_42', 5, true)
        ]);

        $query = new ListRootCategoriesWithCount(
            -2,
            false,
            1,
            'en_US',
            42
        );
        $this->handle($query)->shouldBeLike([
            new RootCategory(42, 'code_42', 'label_42', 5, true)
        ]);
    }
}

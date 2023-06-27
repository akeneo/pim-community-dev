<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCountHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var UserContext */
    private $userContext;

    /** @var ListRootCategoriesWithCountIncludingSubCategories */
    private $listAndCountIncludingSubCategories;

    /** @var ListRootCategoriesWithCountNotIncludingSubCategories */
    private $listAndCountNotIncludingSubCategories;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UserContext $userContext
     * @param ListRootCategoriesWithCountIncludingSubCategories $listAndCountIncludingSubCategories
     * @param ListRootCategoriesWithCountNotIncludingSubCategories $listAndCountNotIncludingSubCategories
     */
    public function __construct(
        CategoryRepositoryInterface                          $categoryRepository,
        UserContext                                          $userContext,
        ListRootCategoriesWithCountIncludingSubCategories    $listAndCountIncludingSubCategories,
        ListRootCategoriesWithCountNotIncludingSubCategories $listAndCountNotIncludingSubCategories
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->userContext = $userContext;
        $this->listAndCountIncludingSubCategories = $listAndCountIncludingSubCategories;
        $this->listAndCountNotIncludingSubCategories = $listAndCountNotIncludingSubCategories;
    }

    /**
     * @param ListRootCategoriesWithCount $query
     *
     * @return RootCategory[]
     */
    public function handle(ListRootCategoriesWithCount $query): array
    {
        $defaultCategoryTreeId = $query->categoryTreeIdSelectedAsFilter() ?? $this->userContext->getAccessibleUserTree()?->getId();

        if ($defaultCategoryTreeId === null) {
            return [];
        }

        $selectedCategoryTree = $this->getCategoryTreeFromSelectedSubCategory(
            $query->categoryIdSelectedAsFilter(),
            $defaultCategoryTreeId
        );

        if ($selectedCategoryTree === null) {
            return [];
        }

        return $query->countIncludingSubCategories() ?
            $this->listAndCountIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $selectedCategoryTree->getId()
            ) :
            $this->listAndCountNotIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $selectedCategoryTree->getId()
            );
    }

    private function getCategoryTreeFromSelectedSubCategory(int $subCategoryId, int $defaultCategoryTreeId): CategoryInterface|null
    {
        $selectedCategory = $this->categoryRepository->find($subCategoryId);
        if (null === $selectedCategory) {
            // selected category does not exist in DB, so we get the categoryTree from the default categoryTreeId
            $selectedCategory = $this->categoryRepository->find($defaultCategoryTreeId);
            if ($selectedCategory === null) {
                // we can't find the category tree, so we use the one accessible to the user
                $selectedCategory = $this->userContext->getAccessibleUserTree();
                if ($selectedCategory === null) {
                    // we can't find a proper category tree
                    return null;
                }
            }
        }
        return $this->categoryRepository->find($selectedCategory->getRoot());
    }
}

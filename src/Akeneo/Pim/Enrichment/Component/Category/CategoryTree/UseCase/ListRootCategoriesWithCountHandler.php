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
        $defaultCategoryTreeId = $this->getDefaultCategoryTreeId($query);
        if ($defaultCategoryTreeId === null) {
            return [];
        }

        $rootCategoryToExpand = $this->getRootCategoryToExpandFromSelectedCategory(
            $query->categoryIdSelectedAsFilter(),
            $defaultCategoryTreeId
        );

        if ($rootCategoryToExpand === null) {
            return [];
        }

        return $query->countIncludingSubCategories() ?
            $this->listAndCountIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $rootCategoryToExpand->getId()
            ) :
            $this->listAndCountNotIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $rootCategoryToExpand->getId()
            );
    }

    /**
     * Try to get the categoryTreeId from url parameters.
     * If not set, try to get the root category id from selectedCategoryId in url parameters.
     * If the root category does not exist, try to get the user accessible categoryTreeId.
     * @param ListRootCategoriesWithCount $query
     * @return int|null
     */
    private function getDefaultCategoryTreeId(ListRootCategoriesWithCount $query): ?int
    {
        if (null !== $query->categoryTreeIdSelectedAsFilter()) {
            return $query->categoryTreeIdSelectedAsFilter();
        }

        if (null !== $query->categoryIdSelectedAsFilter()) {
            $selectedCategory = $this->categoryRepository->find($query->categoryIdSelectedAsFilter());
            if (null !== $selectedCategory && null !== $selectedCategory->getRoot()) {
                return $selectedCategory->getRoot();
            }
        }

        return $this->userContext->getAccessibleUserTree()?->getId();
    }

    private function getRootCategoryToExpandFromSelectedCategory(int $subCategoryId, ?int $defaultCategoryTreeId): CategoryInterface|null
    {
        $selectedCategory = $this->categoryRepository->find($subCategoryId);
        if (null !== $selectedCategory) {
            return $this->categoryRepository->find($selectedCategory->getRoot());
        }

        // selected category does not exist in DB, so we get the categoryTree from the default categoryTreeId
        if (null !== $defaultCategoryTreeId) {
            return $this->categoryRepository->find($defaultCategoryTreeId);
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

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
        $categorySelectedAsFilter = -1 !== $query->categoryIdSelectedAsFilter() ?
            $this->categoryRepository->find($query->categoryIdSelectedAsFilter()) : null;

        if (null === $categorySelectedAsFilter) {
            $categorySelectedAsFilter = $this->userContext->getAccessibleUserTree();
            if ($categorySelectedAsFilter === null) {
                return [];
            }
        }
        $rootCategoryIdToExpand = $categorySelectedAsFilter->getRoot();

        $rootCategories = $query->countIncludingSubCategories() ?
            $this->listAndCountIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $rootCategoryIdToExpand
            ) :
            $this->listAndCountNotIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $rootCategoryIdToExpand
            );

        return $rootCategories;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Component\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCountHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var UserContext */
    private $userContext;

    /** @var \Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories */
    private $listAndCountIncludingSubCategories;

    /** @var \Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountNotIncludingSubCategories */
    private $listAndCountNotIncludingSubCategories;

    /**
     * @param CategoryRepositoryInterface                                    $categoryRepository
     * @param UserContext                                                    $userContext
     * @param \Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories    $listAndCountIncludingSubCategories
     * @param \Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountNotIncludingSubCategories $listAndCountNotIncludingSubCategories
     */
    public function __construct(
        CategoryRepositoryInterface                                                                                   $categoryRepository,
        UserContext                                                                                                   $userContext,
        \Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories    $listAndCountIncludingSubCategories,
        \Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountNotIncludingSubCategories $listAndCountNotIncludingSubCategories
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->userContext = $userContext;
        $this->listAndCountIncludingSubCategories = $listAndCountIncludingSubCategories;
        $this->listAndCountNotIncludingSubCategories = $listAndCountNotIncludingSubCategories;
    }

    /**
     * @param ListChildrenCategoriesWithCount $query
     *
     * @return \Akeneo\Category\Infrastructure\Component\CategoryTree\ReadModel\ChildCategory[]
     */
    public function handle(ListChildrenCategoriesWithCount $query): array
    {
        $categoryToExpand = -1 !== $query->childrenCategoryIdToExpand() ?
            $this->categoryRepository->find($query->childrenCategoryIdToExpand()) : null;

        if (null === $categoryToExpand) {
            $categoryToExpand = $this->userContext->getUserProductCategoryTree();
        }

        $categorySelectedAsFilter = -1 !== $query->categoryIdSelectedAsFilter() ?
            $this->categoryRepository->find($query->categoryIdSelectedAsFilter()) : null;

        if (null !== $categorySelectedAsFilter
            && !$this->categoryRepository->isAncestor($categoryToExpand, $categorySelectedAsFilter)) {
            $categorySelectedAsFilter = null;
        }

        $categoryIdSelectedAsFilter = null !== $categorySelectedAsFilter ? $categorySelectedAsFilter->getId() : null;

        $categories = $query->countIncludingSubCategories() ?
            $this->listAndCountIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $categoryToExpand->getId(),
                $categoryIdSelectedAsFilter
            ) :
            $this->listAndCountNotIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $categoryToExpand->getId(),
                $categoryIdSelectedAsFilter
            );

        return $categories;
    }
}

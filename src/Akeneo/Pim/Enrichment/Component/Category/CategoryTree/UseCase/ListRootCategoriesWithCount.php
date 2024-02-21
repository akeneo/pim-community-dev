<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCount
{
    public function __construct(
        private int $categoryIdSelectedAsFilter,
        private bool $countIncludingSubCategories,
        private int $userId,
        private string $translationLocaleCode,
        private int|null $categoryTreeIdSelectedAsFilter = null,
    ) {
    }

    /**
     * The category node selected by the user in a category tree to filter products on the grid.
     * This category is the one displayed as selected. It is used to deduct the category tree displayed (root).
     */
    public function categoryIdSelectedAsFilter(): int
    {
        return $this->categoryIdSelectedAsFilter;
    }

    /**
     * Optional
     * This is the category tree that is selected by the user to filter the product grid.
     * It is useful when the selected node is "All products" or "Unclassified products" as it is then not possible to
     * deduct the category tree from the selected node id.
     */
    public function categoryTreeIdSelectedAsFilter(): ?int
    {
        return $this->categoryTreeIdSelectedAsFilter;
    }

    /**
     * @return bool
     */
    public function countIncludingSubCategories(): bool
    {
        return $this->countIncludingSubCategories;
    }

    /**
     * @return int
     */
    public function userId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function translationLocaleCode(): string
    {
        return $this->translationLocaleCode;
    }
}

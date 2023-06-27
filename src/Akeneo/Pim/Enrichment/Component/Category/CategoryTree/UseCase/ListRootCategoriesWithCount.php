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
     * This category is sub-category that is selected by the user to filter the product grid.
     * This category is the one that is displayed as selected. It is used to deduct the category tree displayed (root).
     * @return int
     */
    public function categoryIdSelectedAsFilter(): int
    {
        return $this->categoryIdSelectedAsFilter;
    }

    /**
     * Optional
     * This is the category tree that is selected by the user to filter the product grid.
     * @return int
     */
    public function categoryTreeIdSelectedAsFilter(): int|null
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

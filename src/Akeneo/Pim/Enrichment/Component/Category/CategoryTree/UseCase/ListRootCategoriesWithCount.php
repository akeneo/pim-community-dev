<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCount
{
    /** @var int */
    private $categoryIdSelectedAsFilter;

    /** @var bool */
    private $countIncludingSubCategories;

    /** @var int */
    private $userId;

    /** @var string */
    private $translationLocaleCode;

    /**
     * @param int    $categoryIdSelectedAsFilter
     * @param bool   $countIncludingSubCategories
     * @param int    $userId
     * @param string $translationLocaleCode
     */
    public function __construct(
        int $categoryIdSelectedAsFilter,
        bool $countIncludingSubCategories,
        int $userId,
        string $translationLocaleCode
    ) {
        $this->categoryIdSelectedAsFilter = $categoryIdSelectedAsFilter;
        $this->countIncludingSubCategories = $countIncludingSubCategories;
        $this->userId = $userId;
        $this->translationLocaleCode = $translationLocaleCode;
    }

    /**
     * This category is the category that is selected by the user to filter the product grid.
     * The tree of this category is the tree that is displayed.
     *
     * @return int
     */
    public function categoryIdSelectedAsFilter(): int
    {
        return $this->categoryIdSelectedAsFilter;
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

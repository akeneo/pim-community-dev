<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCount
{
    /** @var int */
    private $categoryIdToExpand;

    /** @var int */
    private $categoryIdSelectedAsFilter;

    /** @var bool */
    private $countIncludingSubCategories;

    /** @var int */
    private $userId;

    /** @var string */
    private $translationLocaleCode;

    /**
     * @param int    $categoryIdToExpand
     * @param int    $categoryIdSelectedAsFilter
     * @param bool   $countByIncludingSubCategories
     * @param int    $userId
     * @param string $translationLocaleCode
     */
    public function __construct(
        int $categoryIdToExpand,
        int $categoryIdSelectedAsFilter,
        bool $countByIncludingSubCategories,
        int $userId,
        string $translationLocaleCode
    ) {
        $this->categoryIdToExpand = $categoryIdToExpand;
        $this->categoryIdSelectedAsFilter = $categoryIdSelectedAsFilter;
        $this->countIncludingSubCategories = $countByIncludingSubCategories;
        $this->userId = $userId;
        $this->translationLocaleCode = $translationLocaleCode;
    }

    /**
     * The category to display is the category that is chosen by the user to be expanded.
     *
     * Do note that the user can expand a category without selecting it as a filter.
     * Therefore, the category to expand can be different from the selected category.
     *
     * @return int
     */
    public function childrenCategoryIdToExpand(): int
    {
        return $this->categoryIdToExpand;
    }

    /**
     * This category is the category that is selected by the user to filter the product grid.
     * It is useful when:
     *  - the user displays the tree
     *  - selects a category as filter
     *  - goes onto another page
     *  - and then goes back onto the page to display the tree
     *
     * The tree has to be displayed with the category selected as filter, in order to not lose filters when browsing the application.
     *
     * So, we have to return all the children recursively until this selected category.
     * A better solution is to not reload entirely the tree on the front-end part and keep a state of it.
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

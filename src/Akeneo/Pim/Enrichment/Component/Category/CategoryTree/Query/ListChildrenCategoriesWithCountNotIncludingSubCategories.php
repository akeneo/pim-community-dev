<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListChildrenCategoriesWithCountNotIncludingSubCategories
{
    /**
     * @param string   $translationLocaleCode
     * @param int      $userId
     * @param int      $rootCategoryIdIdToExpand
     * @param null|int $categorySelectedAsFilter
     *
     * @return ChildCategory[]
     */
    public function list(
        string $translationLocaleCode,
        int $userId,
        int $rootCategoryIdIdToExpand,
        ?int $categorySelectedAsFilter
    ): array;
}

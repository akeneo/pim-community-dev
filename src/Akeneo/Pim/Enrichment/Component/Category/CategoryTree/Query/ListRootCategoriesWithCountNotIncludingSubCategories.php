<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListRootCategoriesWithCountNotIncludingSubCategories
{
    /**
     * @param string $translationLocaleCode
     * @param int    $userId
     * @param int    $rootCategoryIdToExpand
     *
     * @return RootCategory[]
     */
    public function list(string $translationLocaleCode, int $userId, int $rootCategoryIdToExpand): array;
}

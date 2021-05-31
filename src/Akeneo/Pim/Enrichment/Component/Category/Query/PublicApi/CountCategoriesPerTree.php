<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountCategoriesPerTree
{
    /**
     * For each category tree, calculates the number of selected categories
     * @return array<string, int>
     */
    public function executeWithChildren(array $selectedCategories): array;

    /**
     * For each category tree, calculates the number of selected categories
     * @return array<string, int>
     */
    public function executeWithoutChildren(array $selectedCategories): array;
}

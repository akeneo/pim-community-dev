<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\ServiceApi;

interface CategoryQueryInterface
{
    public function byId(int $categoryId): Category;

    public function byCode(string $categoryCode): Category;

    /**
     * @param array<string> $categoryCodes
     *
     * @return \Generator<Category>
     */
    public function byCodes(array $categoryCodes): \Generator;

    /**
     * @param array<int> $categoryIds
     *
     * @return \Generator<Category>
     */
    public function byIds(array $categoryIds): \Generator;
}

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
}

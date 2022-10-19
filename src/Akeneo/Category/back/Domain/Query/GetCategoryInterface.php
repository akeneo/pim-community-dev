<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\Model\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryInterface
{
    public function byId(int $categoryId): ?Category;

    public function byCode(string $categoryCode): ?Category;

    public function getTrees(): array;
}

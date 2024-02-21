<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetCategoryByIds
{
    /**
     * @param array<int> $categoryIds
     *
     * @return Category[]
     */
    public function __invoke(array $categoryIds): array;
}

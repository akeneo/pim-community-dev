<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoriesInterface
{
    /**
     * @param array<string> $categoryCodes
     *
     * @return array<Category>
     */
    public function afterOffset(array $categoryCodes, int $limit, int $offset, bool $isEnrichedAttributes): array;
}

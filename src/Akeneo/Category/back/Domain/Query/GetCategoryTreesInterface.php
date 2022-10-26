<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\Model\Classification\CategoryTree;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryTreesInterface
{
    /**
     * @return array<CategoryTree>|null
     */
    public function getAll(): ?array;

    /**
     * @param array<int> $categryTreeIds
     *
     * @return array<CategoryTree>|null
     */
    public function byIds(array $categryTreeIds): ?array;
}

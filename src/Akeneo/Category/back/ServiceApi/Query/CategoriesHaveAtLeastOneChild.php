<?php

declare(strict_types=1);

namespace Akeneo\Category\ServiceApi\Query;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoriesHaveAtLeastOneChild
{
    /**
     * @param string[] $parentCategoryCodes
     * @param string[] $childrenCategoryCodes
     */
    public function among(array $parentCategoryCodes, array $childrenCategoryCodes): bool;
}

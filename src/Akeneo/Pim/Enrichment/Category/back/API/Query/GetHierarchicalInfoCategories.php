<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\API\Query;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetHierarchicalInfoCategories
{
    public function isAChildOf(string $parentCategoryCodes, string $childrenCategoryCodes): bool;
}

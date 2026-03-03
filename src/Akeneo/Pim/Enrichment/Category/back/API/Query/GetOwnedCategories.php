<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetOwnedCategories
{
    /**
     * Returns the category codes given in first parameter that are product/product model owners
     * depending on the user group ids.
     *
     * @param string[] $categoryCodes
     * @param int $userId
     * @return string[]
     */
    public function forUserId(array $categoryCodes, int $userId): array;
}

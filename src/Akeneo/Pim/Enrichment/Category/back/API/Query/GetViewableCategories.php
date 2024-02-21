<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetViewableCategories
{
    /**
     * Returns the category codes given in first parameter that the user has read access to
     *
     * @param string[] $categoryCodes
     * @return string[]
     */
    public function forUserId(array $categoryCodes, int $userId): array;
}

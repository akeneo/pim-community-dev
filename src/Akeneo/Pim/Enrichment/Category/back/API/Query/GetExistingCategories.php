<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetExistingCategories
{
    /**
     * Returns the category codes given in first parameter that are existing categories.
     *
     * @param string[] $categoryCodes
     * @return string[]
     */
    public function forCodes(array $categoryCodes): array;
}

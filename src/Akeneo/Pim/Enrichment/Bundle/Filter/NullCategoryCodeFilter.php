<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

class NullCategoryCodeFilter implements CategoryCodeFilterInterface
{
    public function filter(array $codes): array
    {
        return $codes;
    }
}

<?php

namespace Akeneo\Category\back\tests\Integration\Stubs;

use Akeneo\Pim\Enrichment\Bundle\Filter\CategoryCodeFilterInterface;

class AllowAllCategoryCode implements CategoryCodeFilterInterface
{
    public function filter(array $codes): array
    {
        return $codes;
    }
}

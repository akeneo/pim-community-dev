<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query\InMemory;

use Akeneo\Tool\Component\Analytics\IsDemoCatalogQuery;

class IsDemoCatalogInMemory implements IsDemoCatalogQuery
{
    public function fetch(): bool
    {
        return true;
    }
}

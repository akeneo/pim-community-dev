<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query\InMemory;

use Akeneo\Tool\Component\Analytics\ApiConnectionCountQuery;

class ApiConnectionCountInMemory implements ApiConnectionCountQuery
{
    public function fetch(): array
    {
        return [
            'data_source'=> ['tracked' => 0, 'untracked' => 0],
            'data_destination'=> ['tracked' => 0, 'untracked' => 0],
            'other'=> ['tracked' => 0, 'untracked' => 0],
        ];
    }
}

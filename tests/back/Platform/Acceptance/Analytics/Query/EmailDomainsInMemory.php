<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query;

use Akeneo\Tool\Component\Analytics\EmailDomainsQuery;

class EmailDomainsInMemory implements EmailDomainsQuery
{
    public function fetch(): string
    {
        return "example.com,example2.com";
    }
}

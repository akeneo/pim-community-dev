<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Analytics\Query\Sql;

use Pim\Bundle\AnalyticsBundle\Query\EmailDomainsQuery;

class EmailDomainsInMemory implements EmailDomainsQuery
{
    public function fetch(): string
    {
        return "example.com,example2.com";
    }
}

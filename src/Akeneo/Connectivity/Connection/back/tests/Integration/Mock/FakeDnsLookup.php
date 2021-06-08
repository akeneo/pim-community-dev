<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Mock;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;

class FakeDnsLookup implements DnsLookupInterface
{
    public function ip(string $host): ?string
    {
        return '168.212.226.204';
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\DnsLookup;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeDnsLookup implements DnsLookupInterface
{
    private array $ips;

    public function __construct(array $ips = [])
    {
        $this->ips = $ips;
    }

    public function lookupHost(string $host)
    {
        if (0 === count($this->ips)) {
            return false;
        }

        return $this->ips;
    }

    public function setResolvedIps(array $ips): void
    {
        $this->ips = $ips;
    }
}

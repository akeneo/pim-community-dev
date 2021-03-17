<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\DnsLookup;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DnsLookup implements DnsLookupInterface
{
    public function lookupHost(string $host)
    {
        return gethostbynamel($host);
    }
}

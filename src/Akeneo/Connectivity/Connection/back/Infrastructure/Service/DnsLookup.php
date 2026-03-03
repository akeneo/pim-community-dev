<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\DnsLookupInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DnsLookup implements DnsLookupInterface
{
    /**
     * The ip returned by gethostbyname must be validated because when an error occurs in this function,
     * the host is returned.
     */
    public function ip(string $host): ?string
    {
        $ip = \gethostbyname($host);

        $flag = \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6;
        if (!\filter_var($ip, \FILTER_VALIDATE_IP, $flag)) {
            return null;
        }

        return $ip;
    }
}

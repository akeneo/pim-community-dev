<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Network;

class DnsLookup implements DnsLookupInterface
{
    /**
     * The ip returned by gethostbyname must be validated because when an error occurs in this function,
     * the host is returned.
     */
    public function ip(string $host): ?string
    {
        $ip = gethostbyname($host);

        $flag = \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6;
        if (!filter_var($ip, \FILTER_VALIDATE_IP, $flag)) {
            return null;
        }

        return $ip;
    }
}

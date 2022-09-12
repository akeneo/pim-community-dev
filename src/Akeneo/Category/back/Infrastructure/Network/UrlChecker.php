<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Network;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlChecker
{
    private const DOMAIN_BLACKLIST = [
        'localhost',
        'elasticsearch',
        'memcached',
        'object-storage',
        'mysql',
    ];

    /** @param $allowedProtocols string[] */
    public function __construct(
        private array $allowedProtocols,
        private DnsLookupInterface $dnsLookup
    ) {
    }

    /** @return string[] */
    public function getAllowedProtocols(): array
    {
        return $this->allowedProtocols;
    }

    public function isProtocolAllowed(string $protocol): bool
    {
        return \in_array(\strtolower($protocol), $this->allowedProtocols);
    }

    public function isDomainAllowed(string $host): bool
    {
        $host = \strtolower($host);

        if (\in_array($host, self::DOMAIN_BLACKLIST)) {
            return false;
        }

        $ip = $this->dnsLookup->ip($host);
        if (null === $ip) {
            return true;
        }

        return !$this->isInPrivateRange($ip);
    }

    private function isInPrivateRange(string $ip): bool
    {
        return !\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE);
    }
}

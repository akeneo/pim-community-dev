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

class UrlChecker
{
    private const DOMAIN_BLACKLIST = [
        'localhost',
        'elasticsearch',
        'memcached',
        'object-storage',
        'mysql',
    ];

    /** @var string[] */
    private array $networkWhitelist;

    /** @param $allowedProtocols string[] */
    public function __construct(
        private array $allowedProtocols,
        private DnsLookupInterface $dnsLookup,
        private IpMatcher $ipMatcher,
        string $networkWhitelist = ''
    ) {
        $this->networkWhitelist = empty($networkWhitelist) ? [] : \explode(',', $networkWhitelist);
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

        if ($this->isInWhitelist($ip)) {
            return true;
        }
        return !$this->isInPrivateRange($ip);
    }

    private function isInPrivateRange(string $ip): bool
    {
        return !\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE);
    }

    private function isInWhitelist(string $ip): bool
    {
        if (empty($this->networkWhitelist)) {
            return false;
        }

        return $this->ipMatcher->match($ip, $this->networkWhitelist);
    }
}

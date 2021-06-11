<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\IpMatcherInterface;
use Symfony\Component\HttpFoundation\IpUtils;

class IpMatcher implements IpMatcherInterface
{
    /**
     * @param string[] $whitelist
     */
    public function match(string $ip, array $whitelist): bool
    {
        return IpUtils::checkIp($ip, $whitelist);
    }
}

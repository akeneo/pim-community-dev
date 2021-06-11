<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

interface IpMatcherInterface
{
    /**
     * @param string[] $whitelist
     */
    public function match(string $ip, array $whitelist): bool;
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\IpMatcherInterface;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

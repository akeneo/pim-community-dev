<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Network;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\IpUtils;

class IpMatcher
{
    /**
     * @param string[] $whitelist
     */
    public function match(string $ip, array $whitelist): bool
    {
        return IpUtils::checkIp($ip, $whitelist);
    }
}

<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Infrastructure\Network\DnsLookupInterface;

class FakeDnsLookup implements DnsLookupInterface
{
    public function ip(string $host): ?string
    {
        return '168.212.226.204';
    }
}

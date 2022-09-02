<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

class LRUCachedAssetFamilyExists implements AssetFamilyExistsInterface
{
    private LRUCache $cache;

    public function __construct(private AssetFamilyExistsInterface $assetFamilyExists)
    {
        $this->cache = new LRUCache(100);
    }

    public function withIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier, bool $caseSensitive = true): bool
    {
        $key = (string)$assetFamilyIdentifier;

        return $this->cache->getForKey(
            $key,
            fn () => $this->assetFamilyExists->withIdentifier($assetFamilyIdentifier, $caseSensitive)
        );
    }
}

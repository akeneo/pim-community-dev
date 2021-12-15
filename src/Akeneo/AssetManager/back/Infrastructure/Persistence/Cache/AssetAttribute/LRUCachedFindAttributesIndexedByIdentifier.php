<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetAttribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\SqlFindAttributesIndexedByIdentifier;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCachedFindAttributesIndexedByIdentifier implements FindAttributesIndexedByIdentifierInterface
{
    private LRUCache $cache;

    public function __construct(private SqlFindAttributesIndexedByIdentifier $findAttributesIndexedByIdentifier)
    {
        $this->cache = new LRUCache(100);
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $key = (string)$assetFamilyIdentifier;

        return $this->cache->getForKey(
            $key,
            fn () => $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)
        );
    }
}

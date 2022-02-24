<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\Cache;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\Connection;

final class LRUCachedIsAttributeEditable implements IsAttributeEditable
{
    private LRUCache $cache;

    public function __construct(private Connection $connection)
    {
        $this->cache = new LRUCache(1000);
    }

    public function forCode(string $attributeCode, int $userId): bool
    {
        $attributeCodes = $this->cache->getForKey(
            (string) $userId,
            fn (string $userId): array => $this->getEditableAttributeCodes($userId)
        );

        return \in_array($attributeCode, $attributeCodes);
    }

    /**
     * @return string[]
     */
    private function getEditableAttributeCodes(string $userId): array
    {
        return $this->connection->executeQuery(
            'SELECT code from pim_catalog_attribute'
        )->fetchFirstColumn();
    }
}

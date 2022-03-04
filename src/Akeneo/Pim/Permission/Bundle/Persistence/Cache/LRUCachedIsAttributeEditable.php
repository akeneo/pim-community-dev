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
        $query = <<<SQL
        SELECT attribute.code
        FROM pim_catalog_attribute attribute
        WHERE EXISTS (
            SELECT * FROM pimee_security_attribute_group_access attribute_access
            INNER JOIN oro_user_access_group user_access_group on attribute_access.user_group_id = user_access_group.group_id AND user_access_group.user_id = :userId
            WHERE attribute_access.attribute_group_id = attribute.group_id
            AND attribute_access.edit_attributes = 1
        )
SQL;

        return $this->connection->executeQuery($query, [
            'userId' => $userId,
        ])->fetchFirstColumn();
    }
}

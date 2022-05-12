<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Channel\Infrastructure\Query;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use Doctrine\DBAL\Connection;

final class LRUCachedIsLocaleEditable implements IsLocaleEditable
{
    private const CACHE_SIZE = 100;

    private LRUCache $cache;

    public function __construct(private Connection $connection)
    {
        $this->cache = new LRUCache(self::CACHE_SIZE);
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(string $localeCode, int $userId): bool
    {
        $editableLocaleCodes = $this->cache->getForKey(
            (string) $userId,
            fn (string $userId) => $this->getEditableLocaleCodesForUser((int) $userId)
        );

        return \in_array($localeCode, $editableLocaleCodes);
    }

    /**
     * @return string[]
     */
    private function getEditableLocaleCodesForUser(int $userId): array
    {
        $sql = <<<SQL
        SELECT DISTINCT l.code
        FROM pim_catalog_locale l
            INNER JOIN pimee_security_locale_access la ON l.id = la.locale_id
            INNER JOIN oro_user_access_group ug ON ug.group_id = la.user_group_id
        WHERE ug.user_id = :user_id
            AND la.edit_products = 1
            AND l.is_activated = 1
        SQL;

        return $this->connection->fetchFirstColumn($sql, ['user_id' => $userId]);
    }
}

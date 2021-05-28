<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Connection;

class SqlFindValueKeyCollection implements FindValueKeyCollectionInterface
{
    private Connection $sqlConnection;

    private array $cachedResult = [];

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function clearCache(): void
    {
        $this->cachedResult = [];
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): ValueKeyCollection
    {
        if (!isset($this->cachedResult[$assetFamilyIdentifier->normalize()])) {
            $this->cachedResult[$assetFamilyIdentifier->normalize()] = $this->fetch($assetFamilyIdentifier);
        }

        return $this->cachedResult[$assetFamilyIdentifier->normalize()];
    }

    /**
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     *
     * @return ValueKeyCollection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetch(AssetFamilyIdentifier $assetFamilyIdentifier): ValueKeyCollection
    {
        $query = <<<SQL
            SELECT
                CONCAT(
                    mask.identifier,
                    IF(mask.value_per_channel, CONCAT('_', mask.channel_code), ''),
                    IF(mask.value_per_locale, CONCAT('_', mask.locale_code), '')
                 ) as `key`
            FROM (
                SELECT
                    a.identifier,
                    a.value_per_channel,
                    a.value_per_locale,
                    COALESCE(c.code, locale_channel.channel_code) as channel_code,
                    COALESCE(l.code, locale_channel.locale_code) as locale_code
                FROM
                    akeneo_asset_manager_attribute as a
                    LEFT JOIN pim_catalog_channel c ON value_per_channel = 1 AND value_per_locale = 0
                    LEFT JOIN pim_catalog_locale l ON value_per_channel = 0 AND value_per_locale = 1 AND is_activated = 1
                    LEFT JOIN (
                        SELECT
                            c.code as channel_code,
                            l.code as locale_code
                        FROM
                            pim_catalog_channel c
                            JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                            JOIN pim_catalog_locale l ON l.id = locale_id
                        WHERE
                            l.is_activated = 1
                    ) as locale_channel ON value_per_channel = 1 AND value_per_locale = 1
                WHERE
                    asset_family_identifier = :asset_family_identifier
            ) as mask;
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'asset_family_identifier' => $assetFamilyIdentifier,
        ]);

        $rows = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $this->createValueKeyCollection($rows);
    }

    private function createValueKeyCollection($rows): ValueKeyCollection
    {
        $valueKeys = [];
        foreach ($rows as $row) {
            $valueKeys[] = ValueKey::createFromNormalized($row);
        }

        return ValueKeyCollection::fromValueKeys($valueKeys);
    }
}

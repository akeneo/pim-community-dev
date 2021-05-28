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
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * This SQL implementation keeps a cache of the fetched masks indexed by their asset family identifier.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRequiredValueKeyCollectionForChannelAndLocales implements FindRequiredValueKeyCollectionForChannelAndLocalesInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifierCollection $localeIdentifierCollectionCollection
    ): ValueKeyCollection {
        Assert::false($localeIdentifierCollectionCollection->isEmpty(), 'The list of locales should not be empty.');

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
                    (
                        SELECT identifier, value_per_channel, value_per_locale
                        FROM akeneo_asset_manager_attribute 
                        WHERE asset_family_identifier = :asset_family_identifier
                        AND is_required = 1 
                    ) as a
                    LEFT JOIN (SELECT code FROM pim_catalog_channel WHERE code = :channel_code) c ON value_per_channel = 1 AND value_per_locale = 0
                    LEFT JOIN (SELECT code FROM pim_catalog_locale WHERE code IN (:locale_codes) AND is_activated = 1) l ON value_per_channel = 0 AND value_per_locale = 1
                    LEFT JOIN (
                        SELECT
                            c.code as channel_code,
                            l.code as locale_code
                        FROM
                            pim_catalog_channel c
                            JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                            JOIN pim_catalog_locale l ON l.id = locale_id
                        WHERE c.code = :channel_code  AND l.code IN (:locale_codes)
                    ) as locale_channel ON value_per_channel = 1 AND value_per_locale = 1
            ) as mask
        WHERE (mask.value_per_channel = 0 OR mask.channel_code IS NOT NULL)
          AND (mask.value_per_locale = 0 OR mask.locale_code IS NOT NULL)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'asset_family_identifier' => $assetFamilyIdentifier,
                'channel_code'                => $channelIdentifier->normalize(),
                'locale_codes'                => $localeIdentifierCollectionCollection->normalize(),
            ],
            [
                'locale_codes' => Connection::PARAM_STR_ARRAY
            ]
        );

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

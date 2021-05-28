<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\ValueKey;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\ValueKey\GetValueKeyForAttributeChannelAndLocaleInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlGetValueKeyForAttributeChannelAndLocale implements GetValueKeyForAttributeChannelAndLocaleInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function fetch(
        AttributeIdentifier $attributeIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): ValueKey {
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
                        WHERE identifier = :attribute_identifier
                    ) as a
                    LEFT JOIN (SELECT code FROM pim_catalog_channel WHERE code = :channel_identifier) c ON value_per_channel = 1 AND value_per_locale = 0
                    LEFT JOIN (SELECT code FROM pim_catalog_locale WHERE code = :locale_identifier AND is_activated = 1) l ON value_per_channel = 0 AND value_per_locale = 1
                    LEFT JOIN (
                        SELECT
                            c.code as channel_code,
                            l.code as locale_code
                        FROM
                            pim_catalog_channel c
                            JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                            JOIN pim_catalog_locale l ON l.id = locale_id
                        WHERE c.code = :channel_identifier  AND l.code = :locale_identifier
                    ) as locale_channel ON value_per_channel = 1 AND value_per_locale = 1
            ) as mask
        WHERE (mask.value_per_channel = 0 OR mask.channel_code IS NOT NULL)
          AND (mask.value_per_locale = 0 OR mask.locale_code IS NOT NULL)
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'attribute_identifier' => $attributeIdentifier->normalize(),
                'channel_identifier' => $channelIdentifier->normalize(),
                'locale_identifier' => $localeIdentifier->normalize(),
            ]
        );

        $row = $statement->fetch(\PDO::FETCH_COLUMN);
        if (empty($row)) {
            throw new \LogicException(
                sprintf(
                    'Expected to find a value key for attribute "%s", channel "%s" and locale "%s"',
                    $attributeIdentifier->normalize(),
                    $channelIdentifier->normalize(),
                    $localeIdentifier->normalize()
                )
            );
        }

        return ValueKey::createFromNormalized($row);
    }
}

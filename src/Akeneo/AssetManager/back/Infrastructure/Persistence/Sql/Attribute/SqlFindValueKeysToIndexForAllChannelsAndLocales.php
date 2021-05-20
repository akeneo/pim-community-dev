<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindValueKeysToIndexForAllChannelsAndLocales implements FindValueKeysToIndexForAllChannelsAndLocalesInterface
{
    private Connection $sqlConnection;

    private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels;

    private array $cachedResult = [];

    public function __construct(
        Connection $sqlConnection,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        if (!isset($this->cachedResult[$assetFamilyIdentifier->normalize()])) {
            $this->cachedResult[$assetFamilyIdentifier->normalize()] = $this->generateSearchMatrixWithValueKeys($assetFamilyIdentifier);
        }

        return $this->cachedResult[$assetFamilyIdentifier->normalize()];
    }

    private function generateSearchMatrixWithValueKeys(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $matrixLocalesPerChannels = $this->findActivatedLocalesPerChannels->findAll();
        $matrix = [];
        foreach ($matrixLocalesPerChannels as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $valueKeys = $this->fetchValueKeys(
                    $assetFamilyIdentifier,
                    ChannelIdentifier::fromCode($channelCode),
                    LocaleIdentifier::fromCode($localeCode)
                );
                $matrix[$channelCode][$localeCode] = $valueKeys;
            }
        }

        return $matrix;
    }

    private function fetchValueKeys(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifier $localeIdentifier
    ): array {
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
                    (SELECT * FROM akeneo_asset_manager_attribute WHERE attribute_type = 'text') as a
                    LEFT JOIN (SELECT * FROM pim_catalog_channel WHERE code = :channel_code) c ON value_per_channel = 1 AND value_per_locale = 0
                    LEFT JOIN (SELECT * FROM pim_catalog_locale WHERE code = :locale_code) l ON value_per_channel = 0 AND value_per_locale = 1 AND is_activated = 1
                    LEFT JOIN (
                        SELECT
                            c.code as channel_code,
                            l.code as locale_code
                        FROM
                            pim_catalog_channel c
                            JOIN pim_catalog_channel_locale cl ON cl.channel_id = c.id
                            JOIN pim_catalog_locale l ON l.id = locale_id
                        WHERE c.code = :channel_code  AND l.code = :locale_code
                    ) as locale_channel ON value_per_channel = 1 AND value_per_locale = 1
                WHERE
                    asset_family_identifier = :asset_family_identifier
            ) as mask;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'asset_family_identifier' => $assetFamilyIdentifier,
            'channel_code'                => $channelIdentifier->normalize(),
            'locale_code'                 => $localeIdentifier->normalize(),
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}

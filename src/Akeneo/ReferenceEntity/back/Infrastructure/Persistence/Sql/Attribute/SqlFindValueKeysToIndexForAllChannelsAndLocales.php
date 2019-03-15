<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindValueKeysToIndexForAllChannelsAndLocales implements FindValueKeysToIndexForAllChannelsAndLocalesInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var FindActivatedLocalesPerChannelsInterface */
    private $findActivatedLocalesPerChannels;

    /** @var array */
    private $cachedResult = [];

    public function __construct(
        Connection $sqlConnection,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        if (!isset($this->cachedResult[$referenceEntityIdentifier->normalize()])) {
            $this->cachedResult[$referenceEntityIdentifier->normalize()] = $this->generateSearchMatrixWithValueKeys($referenceEntityIdentifier);
        }

        return $this->cachedResult[$referenceEntityIdentifier->normalize()];
    }

    private function generateSearchMatrixWithValueKeys(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $matrixLocalesPerChannels = ($this->findActivatedLocalesPerChannels)();
        $matrix = [];
        foreach ($matrixLocalesPerChannels as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $valueKeys = $this->fetchValueKeys(
                    $referenceEntityIdentifier,
                    ChannelIdentifier::fromCode($channelCode),
                    LocaleIdentifier::fromCode($localeCode)
                );
                $matrix[$channelCode][$localeCode] = $valueKeys;
            }
        }

        return $matrix;
    }

    private function fetchValueKeys(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
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
                    (SELECT * FROM akeneo_reference_entity_attribute WHERE attribute_type = 'text') as a
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
                    reference_entity_identifier = :reference_entity_identifier
            ) as mask;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'reference_entity_identifier' => $referenceEntityIdentifier,
            'channel_code'                => $channelIdentifier->normalize(),
            'locale_code'                 => $localeIdentifier->normalize(),
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}

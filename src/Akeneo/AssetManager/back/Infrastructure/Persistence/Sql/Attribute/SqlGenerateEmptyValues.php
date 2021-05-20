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
use Akeneo\AssetManager\Domain\Query\Asset\GenerateEmptyValuesInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

/**
 * Query to generate all empty values given the structure of an asset family
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlGenerateEmptyValues implements GenerateEmptyValuesInterface
{
    private Connection $sqlConnection;

    private AttributeHydratorRegistry $attributeHydratorRegistry;

    public function __construct(Connection $sqlConnection, AttributeHydratorRegistry $attributeHydratorRegistry)
    {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
    }

    /**
     * Return an array of all empty values combination (structure) for the given $assetFamilyIdentifier.
     * [
     *    'value_key1' => [
     *        'attribute' => [],        // fully normalized attribute
     *        'channel' => 'mobile',    // channel code,
     *        'locale' => 'fr_FR',      // locale code
     *        'data' => null
     *    ],
     *    ...
     * ]
     *
     *
     */
    public function generate(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $query = <<<SQL
            SELECT
                mask.*,
                CONCAT(
                    mask.identifier,
                    IF(mask.value_per_channel, CONCAT('_', mask.channel_code), ''),
                    IF(mask.value_per_locale, CONCAT('_', mask.locale_code), '')
                 ) as `key`,
                JSON_OBJECT('locale', mask.locale_code, 'channel', mask.channel_code, 'data', NULL) as `empty_value`
            FROM (
                SELECT
                    a.*,
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

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $values = [];
        foreach ($rows as $row) {
            $key = $row['key'];
            $emptyValue = json_decode($row['empty_value'], true);

            // we remove these values to keep only attribute's columns
            unset($row['key']);
            unset($row['empty_value']);

            $hydrator = $this->attributeHydratorRegistry->getHydrator($row);
            $attribute = $hydrator->hydrate($row);
            $emptyValue['attribute'] = $attribute->normalize();

            $values[$key] = $emptyValue;
        }

        return $values;
    }
}

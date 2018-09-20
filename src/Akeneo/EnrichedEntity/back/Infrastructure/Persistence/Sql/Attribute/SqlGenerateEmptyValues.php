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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\GenerateEmptyValuesInterface;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

/**
 * Query to generate all empty values given the structure of an enriched entity
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlGenerateEmptyValues implements GenerateEmptyValuesInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var AttributeHydratorRegistry */
    private $attributeHydratorRegistry;

    public function __construct(Connection $sqlConnection, AttributeHydratorRegistry $attributeHydratorRegistry)
    {
        $this->sqlConnection = $sqlConnection;
        $this->attributeHydratorRegistry = $attributeHydratorRegistry;
    }

    /**
     * Return an array of all empty values combination (structure) for the given $enrichedEntityIdentifier.
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
    public function __invoke(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $attributeQuery = <<<SQL
        SELECT * FROM akeneo_enriched_entity_attribute WHERE enriched_entity_identifier = :enriched_entity_identifier
SQL;

        $query = <<<SQL
        SELECT
            CONCAT(attribute_per_locale_and_channel.identifier, '_', channel.code, '_', locale.code) as `key`,
            JSON_OBJECT('locale', locale.code, 'channel', channel.code, 'data', NULL) as `empty_value`,
            attribute_per_locale_and_channel.*
            FROM (%1\$s AND value_per_locale = 1 and value_per_channel = 1) as attribute_per_locale_and_channel
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
            JOIN (SELECT code FROM pim_catalog_channel) as channel
        UNION SELECT
            CONCAT(attribute_per_channel.identifier, '_', channel.code) as `key`,
            JSON_OBJECT('locale', NULL, 'channel', channel.code, 'data', NULL) as `empty_value`,
            attribute_per_channel.*
            FROM (%1\$s AND value_per_locale = 0 and value_per_channel = 1) as attribute_per_channel
            JOIN (SELECT code FROM pim_catalog_channel) as channel
        UNION SELECT
            CONCAT(attribute_per_locale.identifier, '_', locale.code) as `key`,
            JSON_OBJECT('locale', locale.code, 'channel', NULL, 'data', NULL) as `empty_value`,
            attribute_per_locale.*
            FROM (%1\$s AND value_per_locale = 1 and value_per_channel = 0) as attribute_per_locale
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
        UNION SELECT
            identifier as `key`,
            JSON_OBJECT('locale', NULL, 'channel', NULL, 'data', NULL) as `empty_value`,
            attribute.*
            FROM (%1\$s AND value_per_locale = 0 and value_per_channel = 0) as attribute
SQL;

        $query = sprintf($query, $attributeQuery);
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
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

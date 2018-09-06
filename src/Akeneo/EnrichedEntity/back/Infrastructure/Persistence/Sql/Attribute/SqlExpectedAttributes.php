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
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ExpectedAttributesInterface;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\AttributeHydratorRegistry;
use Doctrine\DBAL\Connection;

class SqlExpectedAttributes implements ExpectedAttributesInterface
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

    public function __invoke(EnrichedEntityIdentifier $enrichedEntityIdentifier)
    {
        $attributeQuery = <<<SQL
        SELECT identifier FROM akeneo_enriched_entity_attribute WHERE enriched_entity_identifier = :enriched_entity_identifier
SQL;

        $query = <<<SQL
        SELECT
            CONCAT(attribute_per_locale_and_channel.identifier, '_', channel.code, '_', locale.code) as `key`
            FROM (%s AND value_per_locale = 1 and value_per_channel = 1) as attribute_per_locale_and_channel
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
            JOIN (SELECT code FROM pim_catalog_channel) as channel
        UNION SELECT
            CONCAT(attribute_per_channel.identifier, '_', channel.code) as `key`
            FROM (%s AND value_per_locale = 0 and value_per_channel = 1) as attribute_per_channel
            JOIN (SELECT code FROM pim_catalog_channel) as channel
        UNION SELECT
            CONCAT(attribute_per_locale.identifier, '_', locale.code) as `key`
            FROM (%s AND value_per_locale = 1 and value_per_channel = 0) as attribute_per_locale
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
        UNION SELECT
            identifier as `key`
            FROM (%s AND value_per_locale = 0 and value_per_channel = 0) as attribute
SQL;

        $query = sprintf($query, $attributeQuery, $attributeQuery, $attributeQuery, $attributeQuery);
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
        ]);

        $rows = $statement->fetchAll(\PDO::FETCH_COLUMN);

        // Add hydration in next PR
        return $rows;
    }
}

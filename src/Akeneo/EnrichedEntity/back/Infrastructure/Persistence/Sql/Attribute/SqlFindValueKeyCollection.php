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
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Connection;

class SqlFindValueKeyCollection implements FindValueKeyCollectionInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
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

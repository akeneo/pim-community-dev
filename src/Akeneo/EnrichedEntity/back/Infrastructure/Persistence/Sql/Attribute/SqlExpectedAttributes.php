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
use Akeneo\EnrichedEntity\Domain\Query\Attribute\SqlExpectedAttributesInterface;
use Doctrine\DBAL\Connection;

// class SqlExpectedAttributes implements SqlExpectedAttributesInterface
class SqlExpectedAttributes
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withEnrichedEntityIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier, bool $onlyRequired = false, bool $withAttribute = true)
    {
        $attributeQuery = <<<SQL
        SELECT identifier FROM akeneo_enriched_entity_attribute WHERE enriched_entity_identifier = :enriched_entity_identifier
SQL;

        if ($onlyRequired) {
            $attributeQuery .= <<<SQL
           AND is_required = 1
SQL;
        }

        $valueQuery = <<<SQL
        SELECT
            CONCAT(attribute_per_locale_and_channel.identifier, channel.code, locale.code) as value_key, identifier
            FROM (%s AND value_per_locale = 1 and value_per_channel = 1) as attribute_per_locale_and_channel
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
            JOIN (SELECT code FROM pim_catalog_channel) as channel
        UNION SELECT CONCAT(attribute_per_channel.identifier, channel.code), identifier
            FROM (%s AND value_per_locale = 0 and value_per_channel = 1) as attribute_per_channel
            JOIN (SELECT code FROM pim_catalog_channel) as channel
        UNION SELECT CONCAT(attribute_per_locale.identifier, locale.code), identifier
            FROM (%s AND value_per_locale = 1 and value_per_channel = 0) as attribute_per_locale
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
        UNION SELECT CONCAT(attribute.identifier), identifier
            FROM (%s AND value_per_locale = 0 and value_per_channel = 0) as attribute
            JOIN (SELECT code FROM pim_catalog_locale WHERE is_activated = 1) as locale
SQL;

        if ($withAttribute) {
            $attributeJoinQuery = <<<SQL
            SELECT value_key as `key`, attribute.* FROM (%s) as join_value_key JOIN akeneo_enriched_entity_attribute as attribute
            WHERE attribute.identifier = join_value_key.identifier
SQL;
            $query = sprintf($attributeJoinQuery, $valueQuery);
        } else {
            $joinQuery = <<<SQL
            SELECT value_key as `key` FROM (%s) as value_key
SQL;
            $query = sprintf($joinQuery, $valueQuery);
        }

        $query = sprintf($query, $attributeQuery, $attributeQuery, $attributeQuery, $attributeQuery);
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
        ]);
        $result = $statement->fetchAll();

        return $result;
    }
}

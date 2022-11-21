<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Doctrine\DBAL\Connection;

final class SqlFindFlattenAttributes implements FindFlattenAttributesInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(
        string $localeCode,
        int $limit,
        array $attributeTypes = null,
        int $offset = 0,
        string $search = null
    ): array {
        if (0 === $limit || (is_array($attributeTypes) && 0 === count($attributeTypes))) {
            return [];
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $query = <<<SQL
WITH
attribute_group AS (
    SELECT
        attribute_group.id,
        attribute_group.code,
        attribute_group.sort_order,
        COALESCE(translation.label, CONCAT('[', attribute_group.code, ']')) AS label
    FROM pim_catalog_attribute_group attribute_group
        LEFT JOIN pim_catalog_attribute_group_translation translation ON attribute_group.id = translation.foreign_key
                                                                     AND locale = :localeCode
)
SELECT
    attribute.code,
    COALESCE(translation.label, CONCAT('[', attribute.code, ']')) AS label,
    attribute_group.code AS group_code,
    attribute_group.label AS group_label
FROM pim_catalog_attribute attribute
    LEFT JOIN pim_catalog_attribute_translation translation
      ON attribute.id = translation.foreign_key
      AND locale = :localeCode
    LEFT JOIN attribute_group ON attribute_group.id = attribute.group_id
WHERE {searchFilters}
ORDER BY attribute_group.sort_order, attribute.sort_order
LIMIT :limit OFFSET :offset
SQL;

        $searchFilters = [];
        if (null !== $attributeTypes) {
            $searchFilters[] = 'attribute.attribute_type IN (:attributeTypes)';
        }

        if (null !== $search) {
            $search = sprintf('%%%s%%', $search);
            $searchFilters[] = "(translation.label LIKE :search OR attribute.code LIKE :search)";
        }

        $query = strtr($query, [
            '{searchFilters}' => 0 === count($searchFilters) ? 'TRUE' : implode(' AND ', $searchFilters),
        ]);

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'attributeTypes' => $attributeTypes,
                'limit' => $limit,
                'offset' => $offset,
                'localeCode' => $localeCode,
                'search' => $search,
            ],
            [
                'attributeTypes' => Connection::PARAM_STR_ARRAY,
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'localeCode' => \PDO::PARAM_STR,
                'search' => \PDO::PARAM_STR,
            ]
        )->fetchAllAssociative();

        $flattenAttributes = [];
        foreach ($rawResults as $rawResult) {
            $flattenAttributes[] = new FlattenAttribute(
                $rawResult['code'],
                $rawResult['label'],
                $rawResult['group_code'],
                $rawResult['group_label']
            );
        }

        return $flattenAttributes;
    }
}

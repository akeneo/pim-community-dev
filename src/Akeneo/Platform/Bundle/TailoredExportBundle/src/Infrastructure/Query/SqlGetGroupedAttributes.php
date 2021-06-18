<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query;

use Akeneo\Platform\TailoredExport\Domain\Query\GetGroupedAttributes;
use Doctrine\DBAL\Connection;

final class SqlGetGroupedAttributes implements GetGroupedAttributes
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findAttributes(
        string $localeCode,
        int $limit,
        int $offset = 0,
        array $attributeTypes = null,
        string $search = null
    ): array {
        if (is_array($attributeTypes) && 0 === count($attributeTypes)) {
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
    LEFT JOIN pim_catalog_attribute_translation translation ON attribute.id = translation.foreign_key
                                                           AND locale = :localeCode
    LEFT JOIN attribute_group ON attribute_group.id = attribute.group_id
WHERE {searchFilters}
ORDER BY attribute_group.sort_order, attribute.sort_order, attribute.id
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

        return $this->connection->executeQuery(
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
        )->fetchAll();
    }
}

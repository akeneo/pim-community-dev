<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Query\Sql;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetGroupedAttributes;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetGroupedAttributes implements GetGroupedAttributes
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getForAttributeTypes(
        array $attributeTypes,
        string $localeCode,
        int $limit,
        int $offset = 0,
        string $search = null
    ): array {
        if (0 === count($attributeTypes)) {
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
WHERE attribute.attribute_type IN (:attributeTypes) {searchFilter}
ORDER BY attribute_group.sort_order, attribute.sort_order
LIMIT :limit OFFSET :offset
SQL;

        $searchFilter = $search === null
            ? ''
            : sprintf(
                "AND (translation.label LIKE '%%%s%%' OR attribute.code LIKE '%%%s%%')",
                $search,
                $search
            );

        $query = strtr($query, ['{searchFilter}' => $searchFilter]);

        return $this->connection->executeQuery(
            $query,
            ['attributeTypes' => $attributeTypes, 'limit' => $limit, 'offset' => $offset, 'localeCode' => $localeCode],
            [
                'attributeTypes' => Connection::PARAM_STR_ARRAY,
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'localeCode' => \PDO::PARAM_STR,
            ]
        )->fetchAll();
    }
}

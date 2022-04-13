<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Infrastructure\Query\Sql;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Category\Domain\Query\ConnectorCategory;
use Akeneo\Pim\Enrichment\Category\Domain\Query\FindCategoryCodes;
use Akeneo\Pim\Enrichment\Category\Domain\Query\CategoryQuery;
use Akeneo\Pim\Enrichment\Category\Domain\Query\FindConnectorCategories;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindConnectorCategories implements FindConnectorCategories
{
    public function __construct(private Connection $connection, private FindCategoryCodes $findCategoryCodes)
    {
    }

    public function fromQuery(CategoryQuery $query): array
    {
        $categoryCodes = $this->findCategoryCodes->fromQuery($query);
        $sqlQuery = <<<SQL
            WITH position as (
                SELECT code, position 
                FROM(
                    SELECT 
                        category.code, 
                        category.id as category_id, 
                        sibling.id as sibling_id, 
                        ROW_NUMBER() over (PARTITION BY category.parent_id ORDER BY sibling.lft) as position
                    FROM pim_catalog_category category
                        JOIN pim_catalog_category sibling on category.parent_id = sibling.parent_id
                    WHERE category.code IN (:category_codes)
                ) r
                WHERE category_id = sibling_id
            ),
            translation as (
                SELECT code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                    JOIN pim_catalog_category_translation translation 
                        ON translation.foreign_key = category.id
                WHERE code IN (:category_codes)
                GROUP BY category.code
            ),
            parent_code as (
                SELECT category.code, parent.code as parent_code
                FROM pim_catalog_category category
                    JOIN pim_catalog_category parent ON category.parent_id = parent.id 
                WHERE category.code IN (:category_codes)
                GROUP BY code
            )
            SELECT 
                category.code, 
                parent_code.parent_code, 
                COALESCE (position.position, 1) as position, 
                COALESCE(translation.translations, '{}') as json_translations
            FROM 
                pim_catalog_category category
                LEFT JOIN position on position.code = category.code
                LEFT JOIN translation on translation.code = category.code
                LEFT JOIN parent_code on parent_code.code = category.code
            WHERE category.code IN (:category_codes)
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            ['category_codes' => $categoryCodes],
            ['category_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        return array_map(
            function($row) {
                return new ConnectorCategory(
                    $row['code'],
                    $row['parent_code'],
                    (int) $row['position'],
                    json_decode($row['json_translations'], true)
                );
            },
            $result
        );
    }

    public function fromCode(string $code): ?ConnectorCategory {
        $categories = $this->fromQuery(new CategoryQuery(categoryCodes: [$code]));

        return empty($categories) ? null : $categories[0];
    }

}

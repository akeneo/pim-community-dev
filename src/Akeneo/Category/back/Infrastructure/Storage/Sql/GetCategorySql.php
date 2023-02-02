<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategorySql implements GetCategoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function byId(int $categoryId): ?Category
    {
        $condition['sqlWhere'] = 'category.id = :category_id';
        $condition['params'] = ['category_id' => $categoryId];
        $condition['types'] = ['category_id' => \PDO::PARAM_INT];

        return $this->execute($condition);
    }

    public function byCode(string $categoryCode): ?Category
    {
        $condition['sqlWhere'] = 'category.code = :category_code';
        $condition['params'] = ['category_code' => $categoryCode];
        $condition['types'] = ['category_code' => \PDO::PARAM_STR];

        return $this->execute($condition);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \JsonException
     */
    private function execute(array $condition): ?Category
    {
        $sqlWhere = $condition['sqlWhere'];

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
            ),
            template as (
                SELECT category.code as category_code, BIN_TO_UUID(category_template_uuid) as template_uuid
                FROM pim_catalog_category_tree_template template_category
                JOIN pim_catalog_category category ON category.root = template_category.category_tree_id
                JOIN pim_catalog_category_template category_template ON category_template.uuid = template_category.category_template_uuid AND (category_template.is_deactivated IS NULL OR category_template.is_deactivated = 0)
                WHERE $sqlWhere
            )
            SELECT
                category.id,
                category.code,
                category.parent_id,
                category.root as root_id,
                category.lft,
                category.rgt,
                category.lvl,
                category.updated,
                translation.translations,
                category.value_collection,
                template.template_uuid
            FROM 
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
                LEFT JOIN template ON category.code = template.category_code
            WHERE $sqlWhere
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            $condition['params'],
            $condition['types'],
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Category::fromDatabase($result);
    }
}

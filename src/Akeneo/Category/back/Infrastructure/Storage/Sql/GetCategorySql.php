<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

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

    private function execute(array $condition): ?Category
    {
        $sqlWhere = $condition['sqlWhere'];

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE $sqlWhere
            )
            SELECT
                category.id,
                category.code, 
                category.parent_id,
                category.root as root_id,
                translation.translations,
                category.value_collection
            FROM 
                pim_catalog_category category
                LEFT JOIN translation ON translation.code = category.code
            WHERE $sqlWhere
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            $condition['params'],
            $condition['types']
        )->fetchAssociative();

        if (!$result) {
            return null;
        }

        return Category::fromDatabase($result);
    }
}


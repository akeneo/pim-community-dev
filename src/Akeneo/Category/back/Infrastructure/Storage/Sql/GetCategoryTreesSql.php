<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreesSql implements GetCategoryTreesInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function getAll(): ?array
    {
        return $this->execute();
    }

    public function byIds(array $categryTreeIds): ? array
    {
        $condition['sqlAnd'] = 'AND category.id IN (:ids)';
        $condition['params'] = ['ids' => $categryTreeIds];
        $condition['types'] = ['ids' => Connection::PARAM_INT_ARRAY];

        return $this->execute($condition);
    }

    private function execute(array $condition = []): ?array
    {
        $sqlAnd = $condition['sqlAnd'] ?? '';
        $sqlParams = $condition['params'] ?? [];
        $sqlTypes = $condition['types'] ?? [];

        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                JOIN pim_catalog_category_translation translation ON translation.foreign_key = category.id
                WHERE category.parent_id IS NULL 
                AND category.root = category.id
                $sqlAnd
                GROUP BY category.code
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
            WHERE category.parent_id IS NULL 
            AND category.root = category.id
            $sqlAnd
            ORDER BY category.created DESC
        SQL;

        $results = $this->connection->executeQuery(
            $sqlQuery,
            $sqlParams,
            $sqlTypes
        )->fetchAllAssociative();

        if (empty($results)) {
            return null;
        }

        return array_map(function ($result) {
            return new Category(
                new CategoryId((int)$result['id']),
                new Code($result['code']),
                $result['translations'] ?
                    LabelCollection::fromArray(
                        json_decode(
                            $result['translations'],
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        )
                    ) : null,
                $result['parent_id'] ? new CategoryId((int)$result['parent_id']) : null,
                $result['root_id'] ? new CategoryId((int)$result['root_id']) : null,
                $result['value_collection'] ?
                    ValueCollection::fromArray(json_decode($result['value_collection'], true)) : null,
                null
            );
        }, $results);
    }
}


<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\SQL;

use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategory implements GetCategoryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromCode(string $categoryCode): array
    {
        $sqlQuery = <<<SQL
            WITH parent_code as (
                SELECT category.code, parent.code as parent_code
                FROM pim_catalog_category category
                    JOIN pim_catalog_category parent ON category.parent_id = parent.id 
                WHERE category.code IN (:category_codes)
                GROUP BY code
            ),
            translation as (
                SELECT code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                    JOIN pim_catalog_category_translation translation 
                        ON translation.foreign_key = category.id
                WHERE code IN (:category_codes)
                GROUP BY category.code
            ),
            SELECT
                category.id,
                category.code, 
                parent_code.parent_code,
                COALESCE(translation.translations, '{}') as json_translations,
                category.value_collection
            FROM 
                pim_catalog_category category
                LEFT JOIN translation on translation.code = category.code
            WHERE category.code = category_code
        SQL;

        $result = $this->connection->executeQuery(
            $sqlQuery,
            [
                'category_code' => $categoryCode,
            ],
            [
                'category_code' => \PDO::PARAM_STR,
            ]
        )->fetchAssociative();

        return array_map(
            function($row) {
                return new Category(
                    new CategoryId((int)$row['id']),
                    new Code($row['code']),
                    LabelCollection::fromArray(json_decode($row['json_translations'], true)),
                    $row['parent_id'] ? new CategoryId((int)$row['parent_id']) : null
                );
            },
            $result
        );
    }
}

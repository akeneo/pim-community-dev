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
    public function __construct(private Connection $connection)
    {
    }

    public function fromCode(string $categoryCode): ?Category
    {
        $sqlQuery = <<<SQL
            WITH translation as (
                SELECT category.code, JSON_OBJECTAGG(translation.locale, translation.label) as translations
                FROM pim_catalog_category category
                    JOIN pim_catalog_category_translation translation 
                        ON translation.foreign_key = category.id
                WHERE category.code = category_code
            ),
            SELECT
                category.id,
                category.code, 
                category.parent_id,
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

        return new Category(
            new CategoryId((int)$result['id']),
            new Code($result['code']),
            LabelCollection::fromArray(json_decode($result['json_translations'], true)),
            $result['parent_id'] ? new CategoryId((int)$result['parent_id']) : null
        );
    }
}

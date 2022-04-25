<?php

namespace Akeneo\Category\Infrastructure\Storage\Query;

use Akeneo\Category\API\Query\Category;
use Akeneo\Category\API\Query\GetCategory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetCategory implements GetCategory
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function byCode(string $code): ?Category
    {
        $sql = <<<SQL
SELECT category.code, category.updated,  parentCategory.code as parentCode, json_objectagg(labels.locale, labels.label) as translatedLabels
FROM pim_catalog_category as category
LEFT JOIN pim_catalog_category as parentCategory
    ON parentCategory.id = category.parent_id
LEFT JOIN pim_catalog_category_translation as labels
    ON labels.foreign_key = category.id 
WHERE category.code = :code
GROUP BY category.code
SQL;
        $stmt = $this->connection->executeQuery($sql, ['code' => $code]);

        $categoryRaw = $stmt->fetchAssociative();
        if (false === $categoryRaw) {
            return null;
        }

        $dateType = Type::getType(Types::DATETIME_IMMUTABLE);

        $platform = $this->connection->getDatabasePlatform();

        return new Category(
            $categoryRaw['code'],
            $categoryRaw['parentCode'],
            $dateType->convertToPhpValue($categoryRaw['updated'], $platform),
            json_decode($categoryRaw['translatedLabels'], true)
        );
    }
}

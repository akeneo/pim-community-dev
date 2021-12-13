<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetRootCategoryCodesAndLabels
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array<array{code: string, label: string}>
     */
    public function execute(string $locale, string $search, int $offset, int $limit): array
    {
        $query = <<<SQL
SELECT category.code as code, category_label.label as label
FROM pim_catalog_category category
LEFT JOIN pim_catalog_category_translation category_label
    ON category.id = category_label.foreign_key AND category_label.locale = :locale
WHERE category.parent_id IS NULL
AND COALESCE(category_label.label, category.code) LIKE :search
ORDER BY COALESCE(category_label.label, category.code)
LIMIT :limit OFFSET :offset;
SQL;

        return $this->connection->fetchAllAssociative(
            $query,
            [
                'search' => '%'.$search.'%',
                'locale' => $locale,
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'offset' => ParameterType::INTEGER,
                'limit' => ParameterType::INTEGER,
            ]
        );
    }
}

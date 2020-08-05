<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Category;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Doctrine\DBAL\Connection;

class SqlGetCategoryTranslations implements GetCategoryTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byCategoryCodesAndLocale(array $categoryCodes, string $locale): array
    {
        if (empty($categoryCodes)) {
            return [];
        }

        $sql = <<<SQL
SELECT
   c.code AS code,
   trans.label AS label
FROM pim_catalog_category c
INNER JOIN pim_catalog_category_translation trans ON c.id = trans.foreign_key
WHERE c.code IN (:categoryCodes)
AND locale = :locale
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'categoryCodes' => $categoryCodes,
                'locale' => $locale
            ],
            ['categoryCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $categoryTranslations = [];
        foreach ($rows as $row) {
            $categoryTranslations[$row['code']] = $row['label'];
        }

        return $categoryTranslations;
    }
}

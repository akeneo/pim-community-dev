<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use Doctrine\DBAL\Connection;

class SqlGetFamilyTranslations implements GetFamilyTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byFamilyCodesAndLocale(array $familyCodes, string $locale): array
    {
        if (empty($familyCodes)) {
            return [];
        }

        $sql = <<<SQL
SELECT
   f.code AS code,
   trans.label AS label
FROM pim_catalog_family f
INNER JOIN pim_catalog_family_translation trans ON f.id = trans.foreign_key
WHERE f.code IN (:familyCodes)
AND locale = :locale
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'familyCodes' => $familyCodes,
                'locale' => $locale
            ],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $familyTranslations = [];
        foreach ($rows as $row) {
            $familyTranslations[$row['code']] = $row['label'];
        }

        return $familyTranslations;
    }
}

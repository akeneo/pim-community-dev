<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilies;
use Doctrine\DBAL\Connection;

class SqlGetFamilies implements GetFamilies
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function byCodes(array $familyCodes): array
    {
        if (empty($familyCodes)) {
            return [];
        }

        $sql = <<<SQL
WITH family_label as (
    SELECT family.code AS family_code, JSON_OBJECTAGG(translation.locale, translation.label) AS labels
    FROM pim_catalog_family family
        LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key
    WHERE family.code IN (:familyCodes)
    AND translation.label IS NOT NULL
    AND translation.label != ''
    GROUP BY family.code
),
family_attribute as (
    SELECT family.code AS family_code, attribute.code AS attribute_code
    FROM pim_catalog_family family
        INNER JOIN pim_catalog_family_attribute family_attribute ON family_attribute.family_id = family.id
        INNER JOIN pim_catalog_attribute attribute ON attribute.id = family_attribute.attribute_id
    WHERE family.code IN (:familyCodes)
)
SELECT 
    family.code, 
    family_label.labels, 
    JSON_ARRAYAGG(family_attribute.attribute_code) AS attributeCodes
FROM pim_catalog_family family
    LEFT JOIN family_label ON family.code = family_label.family_code
    INNER JOIN family_attribute ON family_attribute.family_code = family.code
WHERE family.code IN (:familyCodes)
GROUP BY family.code
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'familyCodes' => $familyCodes,
            ],
            ['familyCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $families = [];
        foreach ($rows as $row) {
            $families[$row['code']] = new Family(
                $row['code'],
                $row['labels'] !== null ? json_decode($row['labels'], true) : [],
                json_decode($row['attributeCodes'], true)
            );
        }

        return $families;
    }

    public function byCode(string $familyCode): ?Family
    {
        $byCodes = $this->byCodes([$familyCode]);

        return $byCodes[$familyCode] ?? null;
    }
}

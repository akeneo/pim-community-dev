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
SELECT
   family.code AS code,
   COALESCE(JSON_OBJECTAGG(trans.locale, trans.label), JSON_ARRAY()) AS labels,
   JSON_ARRAYAGG(attribute.code) AS attributeCodes
FROM pim_catalog_family family
INNER JOIN pim_catalog_family_translation trans ON family.id = trans.foreign_key
INNER JOIN pim_catalog_family_attribute family_attribute ON family_attribute.family_id = family.id
INNER JOIN pim_catalog_attribute attribute ON attribute.id = family_attribute.attribute_id
WHERE family.code IN (:familyCodes)
GROUP BY family.code, attribute.code
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
                json_decode($row['labels'], true),
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

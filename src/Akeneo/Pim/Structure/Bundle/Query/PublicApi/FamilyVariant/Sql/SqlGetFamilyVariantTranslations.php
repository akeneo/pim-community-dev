<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\FamilyVariant\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use Doctrine\DBAL\Connection;

class SqlGetFamilyVariantTranslations implements GetFamilyVariantTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byFamilyVariantCodesAndLocale(array $familyVariantCodes, string $locale): array
    {
        if (empty($familyVariantCodes)) {
            return [];
        }

        $sql = <<<SQL
SELECT
   fv.code AS code,
   trans.label AS label
FROM pim_catalog_family_variant fv
INNER JOIN pim_catalog_family_variant_translation trans ON fv.id = trans.foreign_key
WHERE fv.code IN (:familyVariantCodes)
AND locale = :locale
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'familyVariantCodes' => $familyVariantCodes,
                'locale' => $locale
            ],
            ['familyVariantCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $familyVariantTranslations = [];
        foreach ($rows as $row) {
            $familyVariantTranslations[$row['code']] = $row['label'];
        }

        return $familyVariantTranslations;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Doctrine\DBAL\Connection;

class SqlGetAssociationTypeTranslations implements GetAssociationTypeTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byAssociationTypeCodeAndLocale(array $associationTypeCodes, string $locale): array
    {
        if (empty($associationTypeCodes)) {
            return [];
        }

        $sql = <<<SQL
SELECT
   at.code AS code,
   trans.label AS label
FROM pim_catalog_association_type at
INNER JOIN pim_catalog_association_type_translation trans ON at.id = trans.foreign_key
WHERE at.code IN (:associationTypeCodes)
AND locale = :locale
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'associationTypeCodes' => $associationTypeCodes,
                'locale' => $locale
            ],
            ['associationTypeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $associationTypeTranslations = [];
        foreach ($rows as $row) {
            $associationTypeTranslations[$row['code']] = $row['label'];
        }

        return $associationTypeTranslations;
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\AssociationType;

use Doctrine\DBAL\Connection;

class SqlGetAssociationTypeLabels
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAssociationTypeCodes(array $associationTypeCodes): array
    {
        $sql = <<<SQL
SELECT
   at.code AS code,
   trans.label AS label,
   trans.locale AS locale
FROM pim_catalog_association_type at
INNER JOIN pim_catalog_association_type_translation trans ON at.id = trans.foreign_key
WHERE at.code IN (:associationTypeCodes)
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['associationTypeCodes' => $associationTypeCodes],
            ['associationTypeCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']][$row['locale']] = $row['label'];
        }

        return $result;
    }
}

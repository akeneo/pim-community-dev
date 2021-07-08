<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypes;
use Doctrine\DBAL\Connection;

final class SqlGetAssociationTypes implements GetAssociationTypes
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $localeCode, int $limit, int $offset = 0, string $search = null): array
    {
        if ($limit === 0) return [];
        if ($offset < 0) {
            $offset = 0;
        }

        $query = <<<SQL
SELECT association_type.code AS code, COALESCE(translation.label, CONCAT('[', association_type.code, ']')) AS label
FROM pim_catalog_association_type association_type
LEFT JOIN pim_catalog_association_type_translation translation
  ON association_type.id = translation.foreign_key
  AND translation.locale = :localeCode
WHERE {searchFilters}
ORDER BY association_type.code
LIMIT :limit OFFSET :offset
SQL;

        $searchFilters = [];
        if (null !== $search) {
            $search = sprintf('%%%s%%', $search);
            $searchFilters[] = "(translation.label LIKE :search OR association_type.code LIKE :search)";
        }

        $query = strtr($query, [
            '{searchFilters}' => 0 === count($searchFilters) ? 'TRUE' : implode(' AND ', $searchFilters),
        ]);

        $rawResults = $this->connection->executeQuery(
            $query,
            [
                'limit' => $limit,
                'offset' => $offset,
                'localeCode' => $localeCode,
                'search' => $search,
            ],
            [
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
                'localeCode' => \PDO::PARAM_STR,
                'search' => \PDO::PARAM_STR,
            ]
        )->fetchAll();

        $associationTypes = [];
        foreach ($rawResults as $rawResult) {
            $associationType = new AssociationType();
            $associationType->code = $rawResult['code'];
            $associationType->label = $rawResult['label'];

            $associationTypes[] = $associationType;
        }

        return $associationTypes;
    }
}

<?php

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyQuery;
use Doctrine\DBAL\Connection;

class SqlFindFamilyCodes implements FindFamilyCodes
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(FindFamilyQuery $query): array
    {
        $searchLanguageCondition = null !== $query->searchLanguage ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $query->includeCodes ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($query->excludeCodes) ? 'AND code NOT IN (:exclude_codes)' : '';
        $limitClause = null !== $query->limit ? 'LIMIT :limit' : '';
        $offsetClause = null !== $this->getOffset($query->page, $query->limit) ? 'OFFSET :offset' : '';

        $sql = <<<SQL
            SELECT DISTINCT family.code
            FROM pim_catalog_family family
            LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key $searchLanguageCondition
            WHERE (family.code LIKE :search OR translation.label LIKE :search)
                $includeCondition
                $excludeCondition
            ORDER BY family.code
            $limitClause
            $offsetClause
        SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'search' => sprintf('%%%s%%', $query->search),
                'locale_code' => $query->searchLanguage,
                'include_codes' => $query->includeCodes,
                'exclude_codes' => $query->excludeCodes,
                'limit' => $query->limit,
                'offset' => $this->getOffset($query->page, $query->limit),
            ],
            [
                'search' => \PDO::PARAM_STR,
                'locale_code' => \PDO::PARAM_STR,
                'include_codes' => Connection::PARAM_STR_ARRAY,
                'exclude_codes' => Connection::PARAM_STR_ARRAY,
                'limit' => \PDO::PARAM_INT,
                'offset' => \PDO::PARAM_INT,
            ]
        );

        return $statement->fetchFirstColumn();
    }

    private function getOffset(?int $page, ?int $limit): ?int
    {
        if (null === $page || null === $limit) {
            return null;
        }

        return ($page - 1) * $limit;
    }
}

<?php

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query;

use Akeneo\Pim\Structure\Family\API\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\API\Query\FindFamilyCodes;
use Doctrine\DBAL\Connection;

class SqlFindFamilyCodes implements FindFamilyCodes
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(FamilyQuery $query): array
    {
        $sql = $this->getSql($query);
        $params = $this->getParams($query);
        $types = $this->getTypes();

        return $this->fetchFamilyCodes($sql, $params, $types);
    }

    private function getSql(FamilyQuery $query): string
    {
        $searchLanguageCondition = null !== $query->searchLanguage ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $query->includeCodes ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($query->excludeCodes) ? 'AND code NOT IN (:exclude_codes)' : '';
        $limitClause = null !== $query->limit ? 'LIMIT :limit' : '';
        $offsetClause = null !== $this->getOffset($query->page, $query->limit) ? 'OFFSET :offset' : '';

        return <<<SQL
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
    }

    private function getParams(FamilyQuery $query): array
    {
        return [
            'search' => sprintf('%%%s%%', $query->search),
            'locale_code' => $query->searchLanguage,
            'include_codes' => $query->includeCodes,
            'exclude_codes' => $query->excludeCodes,
            'limit' => $query->limit,
            'offset' => $this->getOffset($query->page, $query->limit),
        ];
    }

    private function getTypes(): array
    {
        return [
            'search' => \PDO::PARAM_STR,
            'locale_code' => \PDO::PARAM_STR,
            'include_codes' => Connection::PARAM_STR_ARRAY,
            'exclude_codes' => Connection::PARAM_STR_ARRAY,
            'limit' => \PDO::PARAM_INT,
            'offset' => \PDO::PARAM_INT,
        ];
    }

    private function fetchFamilyCodes(string $sql, array $params, array $types): array
    {
        return $this->connection->executeQuery($sql, $params, $types)->fetchFirstColumn();
    }

    private function getOffset(?int $page, ?int $limit): ?int
    {
        if (null === $page || null === $limit) {
            return null;
        }

        return ($page - 1) * $limit;
    }
}

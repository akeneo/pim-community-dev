<?php

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyCodes;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyQuery;
use Doctrine\DBAL\Connection;

class SqlCountFamilyCodes implements CountFamilyCodes
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(CountFamilyQuery $query): int
    {
        $searchLanguageCondition = null !== $query->searchLanguage ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $query->includeCodes ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($query->excludeCodes) ? 'AND code NOT IN (:exclude_codes)' : '';

        $sql = <<<SQL
            SELECT count(DISTINCT family.code)
            FROM pim_catalog_family family
            LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key $searchLanguageCondition
            WHERE (family.code LIKE :search OR translation.label LIKE :search)
                $includeCondition
                $excludeCondition
            ORDER BY family.code
        SQL;

        $params = [
            'search' => sprintf('%%%s%%', $query->search),
            'locale_code' => $query->searchLanguage,
            'include_codes' => $query->includeCodes,
            'exclude_codes' => $query->excludeCodes,
        ];

        $types = [
            'search' => \PDO::PARAM_STR,
            'locale_code' => \PDO::PARAM_STR,
            'include_codes' => Connection::PARAM_STR_ARRAY,
            'exclude_codes' => Connection::PARAM_STR_ARRAY,
        ];

        return $this->fetchCount($sql, $params, $types);
    }

    private function fetchCount(string $sql, array $params, array $types): int
    {
        return $this->connection->executeQuery($sql, $params, $types)->fetchOne();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query\Sql;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQueryPagination;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindFamilyCodes implements FindFamilyCodes
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(FamilyQuery $query): array
    {
        $searchLocaleCondition = null !== $query->search?->labelLocale ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $query->includeCodes ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($query->excludeCodes) ? 'AND code NOT IN (:exclude_codes)' : '';
        $limitClause = null !== $query->pagination?->limit ? 'LIMIT :limit' : '';
        $offsetClause = null !== $this->getOffset($query->pagination) ? 'OFFSET :offset' : '';

        $sql = <<<SQL
            SELECT DISTINCT family.code
            FROM pim_catalog_family family
            LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key $searchLocaleCondition
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
                'search' => sprintf('%%%s%%', $query->search?->value),
                'locale_code' => $query->search?->labelLocale,
                'include_codes' => $query->includeCodes,
                'exclude_codes' => $query->excludeCodes,
                'limit' => $query->pagination?->limit,
                'offset' => $this->getOffset($query->pagination),
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

    private function getOffset(?FamilyQueryPagination $pagination): ?int
    {
        if (null === $pagination || null === $pagination->page || null === $pagination->limit) {
            return null;
        }

        return ($pagination->page - 1) * $pagination->limit;
    }
}

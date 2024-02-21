<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query\Sql;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyCodes;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlCountFamilyCodes implements CountFamilyCodes
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function fromQuery(FamilyQuery $query): int
    {
        $searchLocaleCondition = null !== $query->search?->labelLocale ? 'AND translation.locale = :locale_code' : '';
        $includeCondition = null !== $query->includeCodes ? 'AND code IN (:include_codes)' : '';
        $excludeCondition = !empty($query->excludeCodes) ? 'AND code NOT IN (:exclude_codes)' : '';

        $sql = <<<SQL
            SELECT count(DISTINCT family.code)
            FROM pim_catalog_family family
            LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key $searchLocaleCondition
            WHERE (family.code LIKE :search OR translation.label LIKE :search)
                $includeCondition
                $excludeCondition
            ORDER BY family.code
        SQL;

        $params = [
            'search' => sprintf('%%%s%%', $query->search?->value),
            'locale_code' => $query->search?->labelLocale,
            'include_codes' => $query->includeCodes,
            'exclude_codes' => $query->excludeCodes,
        ];

        $types = [
            'search' => \PDO::PARAM_STR,
            'locale_code' => \PDO::PARAM_STR,
            'include_codes' => Connection::PARAM_STR_ARRAY,
            'exclude_codes' => Connection::PARAM_STR_ARRAY,
        ];

        return (int) $this->connection->executeQuery($sql, $params, $types)->fetchOne();
    }
}

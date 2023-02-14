<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute;

use Akeneo\Pim\Structure\Component\Query\InternalApi\CountAttributes;
use Doctrine\DBAL\Connection;

class SqlCountAttributes implements CountAttributes
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function byCodes(?array $includeCodes = null, ?array $excludeCodes = null): int
    {
        $sql = <<<SQL
SELECT count(*) FROM pim_catalog_attribute %s;
SQL;

        $sqlWherePart = $this->getSqlWherePart($includeCodes, $excludeCodes);
        $sql = sprintf($sql, $sqlWherePart);

        return (int) $this->connection->executeQuery(
            $sql,
            ['include_codes' => $includeCodes, 'exclude_codes' => $excludeCodes],
            ['include_codes' => Connection::PARAM_STR_ARRAY, 'exclude_codes' => Connection::PARAM_STR_ARRAY],
        )->fetchOne();
    }

    private function getSqlWherePart(?array $includeCodes, ?array $excludeCodes): string
    {
        $conditions = [];

        if (null !== $includeCodes) {
            $conditions[] = 'code IN (:include_codes)';
        }

        if (!empty($excludeCodes)) {
            $conditions[] = 'code NOT IN (:exclude_codes)';
        }

        return !empty($conditions) ? sprintf('WHERE %s', join(' AND ', $conditions)) : '';
    }
}

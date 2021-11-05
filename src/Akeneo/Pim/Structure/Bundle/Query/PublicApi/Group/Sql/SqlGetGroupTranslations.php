<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Group\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Doctrine\DBAL\Connection;

class SqlGetGroupTranslations implements GetGroupTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byGroupCodesAndLocale(array $groupCodes, string $locale): array
    {
        if (empty($groupCodes)) {
            return [];
        }

        $sql = <<<SQL
SELECT
   g.code AS code,
   trans.label AS label
FROM pim_catalog_group g
INNER JOIN pim_catalog_group_translation trans ON g.id = trans.foreign_key
WHERE g.code IN (:groupCodes)
AND locale = :locale
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            [
                'groupCodes' => $groupCodes,
                'locale' => $locale
            ],
            ['groupCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $groupTranslations = [];
        foreach ($rows as $row) {
            $groupTranslations[$row['code']] = '' === $row['label'] ? null : $row['label'];
        }

        return $groupTranslations;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\Infrastructure\Query\Sql;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindFamiliesWithLabels implements FindFamiliesWithLabels
{
    public function __construct(
        private Connection $connection,
        private FindFamilyCodes $findFamilyCodes,
    ) {
    }

    public function fromQuery(FamilyQuery $query): array
    {
        $familyCodes = $this->findFamilyCodes->fromQuery($query);
        $labelsByFamilyCode = [];
        foreach ($familyCodes as $familyCode) {
            $labelsByFamilyCode[$familyCode] = [];
        }

        $sql = <<<SQL
            SELECT DISTINCT family.code, translation.label, translation.locale
            FROM pim_catalog_family family
            LEFT JOIN pim_catalog_family_translation translation ON family.id = translation.foreign_key
            WHERE family.code IN (:family_codes)
            ORDER BY family.code
        SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'family_codes' => $familyCodes,
            ],
            [
                'family_codes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $results = $statement->fetchAllAssociative();

        foreach ($results as $result) {
            $familyCode = $result['code'];
            if ($result['label'] !== null) {
                $labelsByFamilyCode[$familyCode][$result['locale']] = $result['label'];
            }
        }

        $familiesWithLabels = [];
        foreach ($labelsByFamilyCode as $familyCode => $labels) {
            $familiesWithLabels[] = new FamilyWithLabels((string) $familyCode, $labels);
        }

        return $familiesWithLabels;
    }
}

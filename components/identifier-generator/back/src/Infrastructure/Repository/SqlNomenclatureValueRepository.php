<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlNomenclatureValueRepository implements NomenclatureValueRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @param array<string, ?string> $values
     */
    public function update(array $values): void
    {
        $familyIds = $this->getExistingFamilyIdsFromFamilyCodes(\array_unique(\array_keys($values)));

        $valuesToUpdateOrInsert = [];
        $familyIdsToDelete = [];
        foreach ($values as $familyCode => $value) {
            $familyId = $familyIds[$familyCode] ?? null;
            if ($familyId) {
                if (null === $value || '' === $value) {
                    $familyIdsToDelete[] = $familyIds[$familyCode];
                } else {
                    $valuesToUpdateOrInsert[] = [
                        'familyId' => $familyIds[$familyCode],
                        'value' => $value
                    ];
                }
            }
        }

        if (\count($familyIdsToDelete)) {
            $this->deleteNomenclatureValues($familyIdsToDelete);
        }

        if (\count($valuesToUpdateOrInsert)) {
            $this->insertOrUpdateNomenclatureValues($valuesToUpdateOrInsert);
        }
    }

    public function get(string $familyCode): ?string
    {
        // TODO this method should not exist.
        // We can keep it until CPM-943 is done.
        $sql = <<<SQL
SELECT value FROM pim_catalog_identifier_generator_family_nomenclature n
INNER JOIN pim_catalog_family f ON f.id = n.family_id
WHERE f.code = :family_code
SQL;
        $result = $this->connection->fetchOne($sql, [
            'family_code' => $familyCode
        ]);

        return $result === false ? null : $result;
    }

    /**
     * @param string[] $familyCodes
     * @return array<string, int>
     */
    private function getExistingFamilyIdsFromFamilyCodes(array $familyCodes): array
    {
        $sql = <<<SQL
SELECT code, id
FROM pim_catalog_family f
WHERE f.code IN (:family_codes)
SQL;
        return $this->connection->fetchAllKeyValue($sql, [
            'family_codes' => $familyCodes,
        ], [
            'family_codes' => Connection::PARAM_STR_ARRAY,
        ]);
    }

    /**
     * @param int[] $familyIdsToDelete
     */
    private function deleteNomenclatureValues(array $familyIdsToDelete): void
    {
        $deleteSql = <<<SQL
    DELETE FROM pim_catalog_identifier_generator_family_nomenclature 
    WHERE family_id IN (:family_ids);
    SQL;
        $this->connection->executeStatement($deleteSql, [
            'family_ids' => $familyIdsToDelete,
        ], [
            'family_ids' => Connection::PARAM_INT_ARRAY,
        ]);
    }

    /**
     * @param {familyId: int, value: string}[] $valuesToUpdateOrInsert
     */
    private function insertOrUpdateNomenclatureValues(array $valuesToUpdateOrInsert): void
    {
        $insertOrUpdateSql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_family_nomenclature (family_id, value)
VALUES {{ values }}
ON DUPLICATE KEY UPDATE value = VALUES(value)
SQL;
        $valuesArray = [];
        for ($i = 0; $i < \count($valuesToUpdateOrInsert); $i++) {
            $valuesArray[] = \sprintf('(:familyId%d, :value%d)', $i, $i);
        }
        $statement = $this->connection->prepare(\strtr(
            $insertOrUpdateSql,
            ['{{ values }}' => \join(',', $valuesArray)]
        ));

        foreach ($valuesToUpdateOrInsert as $i => $valueToUpdateOrInsert) {
            $statement->bindParam(\sprintf('familyId%d', $i), $valueToUpdateOrInsert['familyId']);
            $statement->bindParam(\sprintf('value%d', $i), $valueToUpdateOrInsert['value']);
        }

        $statement->executeStatement();
    }
}

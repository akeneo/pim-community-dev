<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFamilyNomenclatureRepository implements FamilyNomenclatureRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function get(): ?NomenclatureDefinition
    {
        $nomenclatureDefinition = $this->getNomenclatureDefinition();
        if (null !== $nomenclatureDefinition) {
            $values = $this->getNomenclatureValues();
            $nomenclatureDefinition = $nomenclatureDefinition->withValues($values);
        }

        return $nomenclatureDefinition;
    }

    public function update(NomenclatureDefinition $nomenclatureDefinition): void
    {
        $this->connection->beginTransaction();

        $this->updateDefinition($nomenclatureDefinition);
        $this->updateValues($nomenclatureDefinition);

        $this->connection->commit();
    }

    /**
     * @param array{
     *     operator?: string,
     *     value?: int,
     *     generate_if_empty?: bool
     * } $jsonResult
     */
    private function fromNormalized(array $jsonResult): NomenclatureDefinition
    {
        return new NomenclatureDefinition(
            $jsonResult['operator'] ?? null,
            $jsonResult['value'] ?? null,
            $jsonResult['generate_if_empty'] ?? null,
        );
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

        $result = $this->connection->fetchAllKeyValue($sql, [
            'family_codes' => $familyCodes,
        ], [
            'family_codes' => Connection::PARAM_STR_ARRAY,
        ]);

        $familyIds = [];
        foreach ($result as $familyCode => $familyId) {
            $familyIds[(string) $familyCode] = \intval($familyId);
        }

        return $familyIds;
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
     * @param array{familyId: int, value: string}[] $valuesToUpdateOrInsert
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

    private function updateDefinition(NomenclatureDefinition $nomenclatureDefinition): void
    {
        $sql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_nomenclature_definition (property_code, definition)
VALUES(:property_code, :definition)
ON DUPLICATE KEY UPDATE definition = :definition
SQL;

        Assert::notNull($nomenclatureDefinition->operator());
        Assert::notNull($nomenclatureDefinition->value());
        Assert::notNull($nomenclatureDefinition->generateIfEmpty());

        $this->connection->executeStatement($sql, [
            'property_code' => FamilyProperty::TYPE,
            'definition' => \json_encode([
                'operator' => $nomenclatureDefinition->operator(),
                'value' => $nomenclatureDefinition->value(),
                'generate_if_empty' => $nomenclatureDefinition->generateIfEmpty(),
            ]),
        ]);
    }

    private function updateValues(NomenclatureDefinition $nomenclatureDefinition): void
    {
        $familyIds = $this->getExistingFamilyIdsFromFamilyCodes(
            \array_unique(\array_keys($nomenclatureDefinition->values()))
        );

        $valuesToUpdateOrInsert = [];
        $familyIdsToDelete = [];
        foreach (($nomenclatureDefinition->values()) as $familyCode => $value) {
            $familyId = $familyIds[$familyCode] ?? null;
            if ($familyId) {
                if (null === $value || '' === $value) {
                    $familyIdsToDelete[] = $familyIds[$familyCode];
                } else {
                    $valuesToUpdateOrInsert[] = [
                        'familyId' => $familyIds[$familyCode],
                        'value' => $value,
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

    /**
     * @return NomenclatureDefinition|null
     * @throws \Doctrine\DBAL\Exception
     */
    private function getNomenclatureDefinition(): ?NomenclatureDefinition
    {
        $sql = <<<SQL
SELECT definition
FROM pim_catalog_identifier_generator_nomenclature_definition
WHERE property_code=:property_code
SQL;
        $definition = $this->connection->fetchOne($sql, [
            'property_code' => FamilyProperty::TYPE,
        ]);
        if (false === $definition) {
            return null;
        }
        Assert::string($definition);

        $jsonResult = \json_decode($definition, true);
        Assert::isArray($jsonResult, \sprintf('Invalid JSON: "%s"', $definition));

        return $this->fromNormalized($jsonResult);
    }

    /**
     * @return array<string, string>
     */
    private function getNomenclatureValues(): array
    {
        $sql = <<<SQL
SELECT f.code, value
FROM pim_catalog_identifier_generator_family_nomenclature n
INNER JOIN pim_catalog_family f ON f.id = n.family_id
SQL;

        $result = $this->connection->fetchAllKeyValue($sql);

        $nomenclatureValues = [];
        foreach ($result as $familyCode => $value) {
            Assert::string($value);
            $nomenclatureValues[(string) $familyCode] = $value;
        }

        return $nomenclatureValues;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlSimpleSelectNomenclatureRepository implements SimpleSelectNomenclatureRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function get(string $attributeCode): ?NomenclatureDefinition
    {
        $nomenclatureDefinition = $this->getNomenclatureDefinition($attributeCode);
        if (null !== $nomenclatureDefinition) {
            $values = $this->getNomenclatureValues($attributeCode);
            $nomenclatureDefinition = $nomenclatureDefinition->withValues($values);
        }

        return $nomenclatureDefinition;
    }

    public function update(string $attributeCode, NomenclatureDefinition $nomenclatureDefinition): void
    {
        $this->connection->beginTransaction();

        $this->updateDefinition($attributeCode, $nomenclatureDefinition);
        $this->updateValues($attributeCode, $nomenclatureDefinition);

        $this->connection->commit();
    }

    /**
     * @param string $attributeCode
     * @return int[]
     */
    private function getOptionIdsFromAttributeCode(string $attributeCode): array
    {
        $sql = <<<SQL
SELECT pim_catalog_attribute_option.id
FROM pim_catalog_attribute_option
WHERE attribute_id = (SELECT id FROM pim_catalog_attribute WHERE code=:attribute_code);
SQL;

        return \array_map('intval', $this->connection->fetchFirstColumn($sql, [
            'attribute_code' => $attributeCode,
        ]));
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
     * @param string $attributeCode
     * @return NomenclatureDefinition|null
     * @throws \Doctrine\DBAL\Exception
     */
    private function getNomenclatureDefinition(string $attributeCode): ?NomenclatureDefinition
    {
        $sql = <<<SQL
SELECT definition
FROM pim_catalog_identifier_generator_nomenclature_definition
WHERE property_code=:property_code
SQL;
        $definition = $this->connection->fetchOne($sql, [
            'property_code' => $attributeCode,
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
    private function getNomenclatureValues(string $attributeCode): array
    {
        $optionIds = $this->getOptionIdsFromAttributeCode($attributeCode);

        $sql = <<<SQL
SELECT ao.code, value
FROM pim_catalog_identifier_generator_simple_select_nomenclature n
INNER JOIN pim_catalog_attribute_option ao ON ao.id = n.option_id
WHERE option_id IN (:option_ids)
SQL;

        $result = $this->connection->fetchAllKeyValue(
            $sql,
            [
                'option_ids' => $optionIds,
            ],
            [
                'option_ids' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $nomenclatureValues = [];
        foreach ($result as $optionCode => $value) {
            Assert::string($value);
            $nomenclatureValues[(string) $optionCode] = $value;
        }

        return $nomenclatureValues;
    }

    private function updateDefinition(string $attributeCode, NomenclatureDefinition $nomenclatureDefinition): void
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
            'property_code' => $attributeCode,
            'definition' => \json_encode([
                'operator' => $nomenclatureDefinition->operator(),
                'value' => $nomenclatureDefinition->value(),
                'generate_if_empty' => $nomenclatureDefinition->generateIfEmpty(),
            ]),
        ]);
    }

    private function updateValues(string $attributeCode, NomenclatureDefinition $nomenclatureDefinition): void
    {
        $attributeOptionCodes = \array_keys($nomenclatureDefinition->values());

        $sql = <<<SQL
SELECT code, id FROM pim_catalog_attribute_option
WHERE code IN (:attributeOptionCodes)
AND attribute_id = (SELECT id FROM pim_catalog_attribute WHERE code=:attributeCode)
SQL;

        $attributeOptionIds = $this->connection->fetchAllKeyValue(
            $sql,
            [
                'attributeOptionCodes' => $attributeOptionCodes,
                'attributeCode' => $attributeCode,
            ],
            [
                'attributeOptionCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $valuesToUpdateOrInsert = [];
        $attributeOptionIdsToDelete = [];
        foreach (($nomenclatureDefinition->values()) as $attributeOptionCode => $value) {
            $attributeOptionId = $attributeOptionIds[$attributeOptionCode] ?? null;
            if ($attributeOptionId) {
                if (null === $value || '' === $value) {
                    $attributeOptionIdsToDelete[] = \intval($attributeOptionId);
                } else {
                    $valuesToUpdateOrInsert[] = [
                        'attributeOptionId' => \intval($attributeOptionId),
                        'value' => $value,
                    ];
                }
            }
        }

        if (\count($attributeOptionIdsToDelete)) {
            $this->deleteNomenclatureValues($attributeOptionIdsToDelete);
        }

        if (\count($valuesToUpdateOrInsert)) {
            $this->insertOrUpdateNomenclatureValues($valuesToUpdateOrInsert);
        }
    }

    /**
     * @param int[] $attributeOptionIdsToDelete
     */
    private function deleteNomenclatureValues(array $attributeOptionIdsToDelete): void
    {
        $deleteSql = <<<SQL
DELETE FROM pim_catalog_identifier_generator_simple_select_nomenclature 
WHERE option_id IN (:option_ids);
SQL;
        $this->connection->executeStatement($deleteSql, [
            'option_ids' => $attributeOptionIdsToDelete,
        ], [
            'option_ids' => Connection::PARAM_INT_ARRAY,
        ]);
    }

    /**
     * @param array{attributeOptionId: int, value: string}[] $valuesToUpdateOrInsert
     */
    private function insertOrUpdateNomenclatureValues(array $valuesToUpdateOrInsert): void
    {
        $insertOrUpdateSql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_simple_select_nomenclature (option_id, value)
VALUES {{ values }}
ON DUPLICATE KEY UPDATE value = VALUES(value)
SQL;
        $valuesArray = [];
        for ($i = 0; $i < \count($valuesToUpdateOrInsert); $i++) {
            $valuesArray[] = \sprintf('(:optionId%d, :value%d)', $i, $i);
        }
        $statement = $this->connection->prepare(\strtr(
            $insertOrUpdateSql,
            ['{{ values }}' => \join(',', $valuesArray)]
        ));

        foreach ($valuesToUpdateOrInsert as $i => $valueToUpdateOrInsert) {
            $statement->bindParam(\sprintf('optionId%d', $i), $valueToUpdateOrInsert['attributeOptionId']);
            $statement->bindParam(\sprintf('value%d', $i), $valueToUpdateOrInsert['value']);
        }

        $statement->executeStatement();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlReferenceEntityNomenclatureRepository implements ReferenceEntityNomenclatureRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly GetAttributes $getAttributes
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
     * @return array<string, string>
     */
    private function getNomenclatureValues(string $attributeCode): array
    {
        $recordIdentifiers = $this->getRecordIdentifiersFromAttributeCode($attributeCode);

        $sql = <<<SQL
SELECT r.code, value
FROM pim_catalog_identifier_generator_ref_entity_nomenclature n
INNER JOIN akeneo_reference_entity_record r ON r.identifier = n.record_identifier
WHERE identifier IN (:record_identifiers)
SQL;

        $result = $this->connection->fetchAllKeyValue(
            $sql,
            [
                'record_identifiers' => $recordIdentifiers,
            ],
            [
                'record_identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $nomenclatureValues = [];
        foreach ($result as $recordCode => $value) {
            Assert::string($value);
            $nomenclatureValues[(string) $recordCode] = $value;
        }

        return $nomenclatureValues;
    }

    /**
     * @return string[]
     */
    private function getRecordIdentifiersFromAttributeCode(string $attributeCode): array
    {
        $refDataName = $this->getRefEntityCodeByAttributeCode($attributeCode);

        $sql = <<<SQL
SELECT akeneo_reference_entity_record.identifier
FROM akeneo_reference_entity_record
WHERE reference_entity_identifier = :refDataName;
SQL;

        return \array_map('strval', $this->connection->fetchFirstColumn($sql, [
            'refDataName' => $refDataName,
        ]));
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
        $recordCodes = \array_keys($nomenclatureDefinition->values());

        $refDataName = $this->getRefEntityCodeByAttributeCode($attributeCode);

        $sql = <<<SQL
SELECT code, identifier FROM akeneo_reference_entity_record
WHERE code IN (:recordCodes)
AND reference_entity_identifier = :refDataName
SQL;

        $recordIdentifiers = $this->connection->fetchAllKeyValue(
            $sql,
            [
                'recordCodes' => $recordCodes,
                'refDataName' => $refDataName,
            ],
            [
                'recordCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $valuesToUpdateOrInsert = [];
        $recordIdentifiersToDelete = [];
        foreach (($nomenclatureDefinition->values()) as $recordCode => $value) {
            $recordIdentifier = $recordIdentifiers[$recordCode] ?? null;
            if ($recordIdentifier) {
                Assert::string($recordIdentifier);
                if (null === $value || '' === $value) {
                    $recordIdentifiersToDelete[] = $recordIdentifier;
                } else {
                    $valuesToUpdateOrInsert[] = [
                        'recordIdentifier' => $recordIdentifier,
                        'value' => $value,
                    ];
                }
            }
        }

        if (\count($recordIdentifiersToDelete)) {
            $this->deleteNomenclatureValues($recordIdentifiersToDelete);
        }

        if (\count($valuesToUpdateOrInsert)) {
            $this->insertOrUpdateNomenclatureValues($valuesToUpdateOrInsert);
        }
    }

    /**
     * @param array<string> $recordIdentifiersToDelete
     */
    private function deleteNomenclatureValues(array $recordIdentifiersToDelete): void
    {
        $deleteSql = <<<SQL
DELETE FROM pim_catalog_identifier_generator_ref_entity_nomenclature 
WHERE record_identifier IN (:recordIdentifiers);
SQL;
        $this->connection->executeStatement($deleteSql, [
            'recordIdentifiers' => $recordIdentifiersToDelete,
        ], [
            'recordIdentifiers' => Connection::PARAM_STR_ARRAY,
        ]);
    }

    /**
     * @param array{recordIdentifier: string, value: string}[] $valuesToUpdateOrInsert
     */
    private function insertOrUpdateNomenclatureValues(array $valuesToUpdateOrInsert): void
    {
        $insertOrUpdateSql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_ref_entity_nomenclature (record_identifier, value)
VALUES {{ values }}
ON DUPLICATE KEY UPDATE value = VALUES(value)
SQL;
        $valuesArray = [];
        for ($i = 0; $i < \count($valuesToUpdateOrInsert); $i++) {
            $valuesArray[] = \sprintf('(:recordIdentifier%d, :value%d)', $i, $i);
        }
        $statement = $this->connection->prepare(\strtr(
            $insertOrUpdateSql,
            ['{{ values }}' => \join(',', $valuesArray)]
        ));

        foreach ($valuesToUpdateOrInsert as $i => $valueToUpdateOrInsert) {
            $statement->bindParam(\sprintf('recordIdentifier%d', $i), $valueToUpdateOrInsert['recordIdentifier']);
            $statement->bindParam(\sprintf('value%d', $i), $valueToUpdateOrInsert['value']);
        }

        $statement->executeStatement();
    }

    private function getRefEntityCodeByAttributeCode(string $attributeCode): string
    {
        $attribute = $this->getAttributes->forCode($attributeCode);

        if (null === $attribute) {
            return '';
        }

        return $attribute->properties()['reference_data_name'];
    }
}

<?php

namespace Pim\Upgrade\Schema;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use PDO;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Version_3_1_20190405150523_ee_replace_record_data_codes_by_identifiers migration es not correct
 * and we must fix record identifiers for single and multiple links
 * @see       PIM-9312
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class Version_3_2_202006191450_fix_record_data_identifiers extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var Connection */
    private $sqlConnection;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sqlConnection = $this->container->get('database_connection');
        $this->referenceEntityRepository = $this->container->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->recordRepository = $this->container->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->findValueKeysByAttributeType = $this->container->get('akeneo_referenceentity.infrastructure.persistence.query.find_value_keys_by_attribute_type');

        /** @var ReferenceEntity $referenceEntity */
        foreach ($this->referenceEntityRepository->all() as $referenceEntity) {
            $nbRecords = $this->recordRepository->countByReferenceEntity($referenceEntity->getIdentifier());
            if (0 === $nbRecords) {
                continue;
            }

            $valueKeysToUpdate = $this->findValueKeysByAttributeType->find(
                $referenceEntity->getIdentifier(),
                ['record', 'record_collection']
            );

            if (0 === count($valueKeysToUpdate)) {
                continue;
            }

            $records = $this->findRecordsByReferenceEntity($referenceEntity->getIdentifier());
            foreach ($records as $record) {
                $valueCollection = json_decode($record['value_collection'], true);
                $valueCollection = $this->replaceCodesByIdentifiers($valueCollection, $valueKeysToUpdate);
                $this->updateRecordValues($record['identifier'], $valueCollection);
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function findRecordsByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): \Iterator
    {
        $sqlQuery = <<<SQL
        SELECT r.identifier, r.value_collection
        FROM akeneo_reference_entity_record r
        WHERE r.reference_entity_identifier = :reference_entity_identifier
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sqlQuery,
            ['reference_entity_identifier' => (string) $referenceEntityIdentifier]
        );

        while (false !== $result = $statement->fetch(PDO::FETCH_ASSOC)) {
            yield $result;
        }
    }

    private function updateRecordValues(
        string $recordIdentifier,
        array $valueCollection
    ): void {
        $update = <<<SQL
        UPDATE akeneo_reference_entity_record
        SET value_collection = :value_collection
        WHERE identifier = :identifier;
SQL;

        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => $recordIdentifier,
                'value_collection' => $valueCollection,
            ],
            [
                'value_collection' => Type::JSON_ARRAY,
            ]
        );

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one record, but %d rows were affected', $affectedRows)
            );
        }
    }

    private function replaceCodesByIdentifiers(
        array $valueCollection,
        array $recordsValueKeys
    ): array {
        $onlyRecordsValues = array_intersect_key($valueCollection, array_flip($recordsValueKeys));

        if (empty($onlyRecordsValues)) {
            return $valueCollection;
        }

        // Get record codes and attributes for which we have to retrieve the identifier
        // We need both code and attribute to build an indexed array
        // because code is not unique among reference entities
        $codes = [];
        $attributes = [];
        foreach ($onlyRecordsValues as $value) {
            $data = is_array($value['data']) ? $value['data'] : [$value['data']];
            $codes = array_merge($codes, $data);
            $attributes[] = $value['attribute'];
        }

        $codes = array_unique($codes);
        $attributes = array_unique($attributes);

        // Retrieve record identifiers
        $recordsIdentifiers = $this->findRecordByCodeAndAttributeIdentifier(
            $codes,
            $attributes
        );

        // Replace codes by identifiers in the value collection
        foreach ($onlyRecordsValues as $valueKey => $value) {
            if (is_array($value['data'])) {
                $valueAttribute = $value['attribute'];
                $value['data'] = array_map(function ($code) use ($recordsIdentifiers, $valueAttribute) {
                    $key = sprintf('%s-%s', $code, $valueAttribute);

                    return isset($recordsIdentifiers[$key]) ? $recordsIdentifiers[$key] : $code;
                }, $value['data']);
            } else {
                $key = sprintf('%s-%s', $value['data'], $value['attribute']);
                $value['data'] = isset($recordsIdentifiers[$key]) ? $recordsIdentifiers[$key] : $value['data'];
            }

            $valueCollection[$valueKey] = $value;
        }

        return $valueCollection;
    }

    private function findRecordByCodeAndAttributeIdentifier(array $recordCodes, array $attributeIdentifiers): array
    {
        $sqlQuery = <<<SQL
        SELECT r.code AS record_code, r.identifier AS record_identifier, a.identifier AS attribute_identifier
        FROM akeneo_reference_entity_record r
        INNER JOIN akeneo_reference_entity_attribute a 
            ON r.reference_entity_identifier = JSON_EXTRACT(a.additional_properties, '$.record_type')
        WHERE r.code IN (:record_codes) AND a.identifier IN (:attribute_identifiers)
        ;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sqlQuery,
            [
                'record_codes' => $recordCodes,
                'attribute_identifiers' => $attributeIdentifiers,
            ],
            [
                'record_codes' => Connection::PARAM_STR_ARRAY,
                'attribute_identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $results = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $result) {
            $key = sprintf('%s-%s', $result['record_code'], $result['attribute_identifier']);
            $results[$key] = $result['record_identifier'];
        }

        return $results;
    }
}

<?php

namespace Pim\Upgrade\Schema;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\ParameterType;
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
        foreach ($valueCollection as $attributeIdentifier => &$value) {
            if (!in_array($attributeIdentifier, $recordsValueKeys)) {
                continue;
            }

            if (is_array($value['data'])) {
                $value['data'] = array_map(function($recordCode) use ($attributeIdentifier) {
                    return $this->replaceCodeByIdentifier($attributeIdentifier, $recordCode);
                }, $value['data']);
            } else {
                $value['data'] = $this->replaceCodeByIdentifier($attributeIdentifier, $value['data']);
            }
        }

        return $valueCollection;
    }

    private function replaceCodeByIdentifier(string $attributeIdentifier, string $recordCode): string
    {
        $query = <<<SQL
        SELECT rec.identifier
        FROM akeneo_reference_entity_attribute att
        JOIN akeneo_reference_entity_record rec
            ON rec.reference_entity_identifier = JSON_UNQUOTE(JSON_EXTRACT(att.additional_properties, '$.record_type'))
        WHERE att.identifier = :attribute_identifier
        AND rec.code = :record_code
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'record_code' => $recordCode,
                'attribute_identifier' => $attributeIdentifier,
            ],
            [
                'record_code' => ParameterType::STRING,
                'attribute_identifier' => ParameterType::STRING,
            ]
        );

        $recordIdentifier = $statement->fetch(FetchMode::COLUMN);

        return $recordIdentifier ?: $recordCode;
    }
}

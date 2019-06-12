<?php

namespace Pim\Upgrade\Schema;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;
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
 * For the 3.1 version, we changed the way we save "Record" and "Record Collection" values.
 * Instead of codes, we now save the Record identifiers.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class Version_3_1_20190405150523_ee_replace_record_data_codes_by_identifiers extends AbstractMigration implements ContainerAwareInterface
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

    /** @var FindIdentifiersByReferenceEntityAndCodesInterface */
    private $findIdentifiersByReferenceEntityAndCodes;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sqlConnection = $this->container->get('database_connection');
        $this->referenceEntityRepository = $this->container->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->recordRepository = $this->container->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->findValueKeysByAttributeType = $this->container->get('akeneo_referenceentity.infrastructure.persistence.query.find_value_keys_by_attribute_type');
        $this->findIdentifiersByReferenceEntityAndCodes = $this->container->get('akeneo_referenceentity.infrastructure.persistence.query.find_identifiers_by_reference_entity_and_codes');

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
                $valueCollection = $this->replaceCodesByIdentifiers($valueCollection, $valueKeysToUpdate, $referenceEntity->getIdentifier());

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
        array $recordsValueKeys,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): array {
        $onlyRecordsValues = array_intersect_key($valueCollection, array_flip($recordsValueKeys));

        if (empty($onlyRecordsValues)) {
            return $valueCollection;
        }

        // Get identifiers for which we have to retrieve the code
        $codes = [];
        foreach ($onlyRecordsValues as $value) {
            $data = is_array($value['data']) ? $value['data'] : [$value['data']];
            $codes = array_merge($codes, $data);
        }

        $codes = array_unique($codes);

        // Retrieve the identifiers
        $indexedIdentifiers = $this->findIdentifiersByReferenceEntityAndCodes->find(
            $referenceEntityIdentifier,
            $codes
        );

        // Replace codes by identifiers in the value collection
        foreach ($onlyRecordsValues as $valueKey => $value) {
            if (is_array($value['data'])) {
                $value['data'] = array_map(function ($code) use ($indexedIdentifiers) {
                    return isset($indexedIdentifiers[$code]) ? $indexedIdentifiers[$code] : $code;
                }, $value['data']);
            } else {
                $value['data'] = isset($indexedIdentifiers[$value['data']]) ? $indexedIdentifiers[$value['data']] : $value['data'];
            }

            $valueCollection[$valueKey] = $value;
        }

        return $valueCollection;
    }
}

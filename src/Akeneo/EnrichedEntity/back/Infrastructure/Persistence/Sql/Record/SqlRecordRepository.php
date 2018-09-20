<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordRepository implements RecordRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var RecordHydratorInterface */
    private $recordHydrator;

    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    public function __construct(
        Connection $sqlConnection,
        RecordHydratorInterface $recordHydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->recordHydrator = $recordHydrator;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM akeneo_enriched_entity_record';
        $statement = $this->sqlConnection->executeQuery($sql);
        $count = (int) $statement->fetchColumn();

        return $count;
    }

    public function create(Record $record): void
    {
        $serializedLabels = $this->getSerializedLabels($record);
        $insert = <<<SQL
        INSERT INTO akeneo_enriched_entity_record 
            (identifier, code, enriched_entity_identifier, labels, image, value_collection)
        VALUES (:identifier, :code, :enriched_entity_identifier, :labels, :image, :value_collection);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $record->getIdentifier(),
                'code' => (string) $record->getCode(),
                'enriched_entity_identifier' => (string) $record->getEnrichedEntityIdentifier(),
                'labels' => $serializedLabels,
                'image' => $record->getImage()->isEmpty() ? null : $record->getImage()->getKey(),
                'value_collection' => $record->getValues()->normalize(),
            ],
            [
                'value_collection' => Type::JSON_ARRAY,
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one record, but %d rows were affected', $affectedRows)
            );
        }
    }

    public function update(Record $record): void
    {
        $serializedLabels = $this->getSerializedLabels($record);
        $update = <<<SQL
        UPDATE akeneo_enriched_entity_record
        SET labels = :labels, image = :image, value_collection = :value_collection
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => $record->getIdentifier(),
                'labels' => $serializedLabels,
                'image' => $record->getImage()->isEmpty() ? null : $record->getImage()->getKey(),
                'value_collection' => $record->getValues()->normalize(),
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

    public function getByEnrichedEntityAndCode(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code
    ): Record {
        $fetch = <<<SQL
        SELECT identifier, code, enriched_entity_identifier, labels, value_collection, fi.image
        FROM akeneo_enriched_entity_record ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE code = :code AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'code' => (string) $code,
                'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
            ]
        );
        $result = $statement->fetch();

        if (!$result) {
            throw RecordNotFoundException::withEnrichedEntityAndCode($enrichedEntityIdentifier, $code);
        }

        return $this->hydrateRecord($result);
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        $fetch = <<<SQL
        SELECT ee.identifier, ee.code, ee.enriched_entity_identifier, ee.labels, ee.value_collection, fi.image
        FROM akeneo_enriched_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'identifier' => (string) $identifier,
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw RecordNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateRecord($result);
    }

    public function deleteByEnrichedEntityAndCode(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code
    ): void {
        $sql = <<<SQL
        DELETE FROM akeneo_enriched_entity_record 
        WHERE code = :code AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'code' => (string) $code,
                'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
            ]
        );

        if ($affectedRows === 0) {
            throw new RecordNotFoundException();
        }
    }

    public function nextIdentifier(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code
    ): RecordIdentifier {
        return RecordIdentifier::create(
            (string) $enrichedEntityIdentifier,
            (string) $code,
            Uuid::uuid4()->toString()
        );
    }

    private function getSerializedLabels(Record $record): string
    {
        $labels = [];
        foreach ($record->getLabelCodes() as $localeCode) {
            $labels[$localeCode] = $record->getLabel($localeCode);
        }

        return json_encode($labels);
    }

    private function getEnrichedEntityIdentifier($result): EnrichedEntityIdentifier
    {
        if (!isset($result['enriched_entity_identifier'])) {
            throw new \LogicException('The record should have an enriched entity identifier');
        }
        $normalizedEnrichedEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['enriched_entity_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return EnrichedEntityIdentifier::fromString($normalizedEnrichedEntityIdentifier);
    }

    private function hydrateRecord($result): Record
    {
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifier($result);
        $valueKeyCollection = ($this->findValueKeyCollection)($enrichedEntityIdentifier);
        $indexedAttributes = ($this->findAttributesIndexedByIdentifier)($enrichedEntityIdentifier);

        return $this->recordHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
    }
}

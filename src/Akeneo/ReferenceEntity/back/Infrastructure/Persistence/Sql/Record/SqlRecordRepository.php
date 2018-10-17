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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        Connection $sqlConnection,
        RecordHydratorInterface $recordHydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->recordHydrator = $recordHydrator;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM akeneo_reference_entity_record';
        $statement = $this->sqlConnection->executeQuery($sql);
        $count = (int) $statement->fetchColumn();

        return $count;
    }

    public function create(Record $record): void
    {
        $serializedLabels = $this->getSerializedLabels($record);
        $insert = <<<SQL
        INSERT INTO akeneo_reference_entity_record
            (identifier, code, reference_entity_identifier, labels, image, value_collection)
        VALUES (:identifier, :code, :reference_entity_identifier, :labels, :image, :value_collection);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $record->getIdentifier(),
                'code' => (string) $record->getCode(),
                'reference_entity_identifier' => (string) $record->getReferenceEntityIdentifier(),
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

        $this->eventDispatcher->dispatch(RecordUpdatedEvent::class, new RecordUpdatedEvent($record->getIdentifier()));
    }

    public function update(Record $record): void
    {
        $serializedLabels = $this->getSerializedLabels($record);
        $update = <<<SQL
        UPDATE akeneo_reference_entity_record
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

        $this->eventDispatcher->dispatch(RecordUpdatedEvent::class, new RecordUpdatedEvent($record->getIdentifier()));
    }

    public function getByReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): Record {
        $fetch = <<<SQL
        SELECT identifier, code, reference_entity_identifier, labels, value_collection, fi.image
        FROM akeneo_reference_entity_record ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'code' => (string) $code,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );
        $result = $statement->fetch();

        if (!$result) {
            throw RecordNotFoundException::withReferenceEntityAndCode($referenceEntityIdentifier, $code);
        }

        return $this->hydrateRecord($result);
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        $fetch = <<<SQL
        SELECT ee.identifier, ee.code, ee.reference_entity_identifier, ee.labels, ee.value_collection, fi.image
        FROM akeneo_reference_entity_record AS ee
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

    public function deleteByReferenceEntity(
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): void {
        $sql = <<<SQL
        DELETE FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $this->sqlConnection->executeUpdate(
            $sql,
            [
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );

        $this->eventDispatcher->dispatch(
            ReferenceEntityRecordsDeletedEvent::class,
            new ReferenceEntityRecordsDeletedEvent($referenceEntityIdentifier)
        );
    }

    public function deleteByReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): void {
        $sql = <<<SQL
        DELETE FROM akeneo_reference_entity_record
        WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'code' => (string) $code,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );

        if (0 === $affectedRows) {
            throw new RecordNotFoundException();
        }

        $this->eventDispatcher->dispatch(
            RecordDeletedEvent::class,
            new RecordDeletedEvent($code, $referenceEntityIdentifier)
        );
    }

    public function nextIdentifier(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): RecordIdentifier {
        return RecordIdentifier::create(
            (string) $referenceEntityIdentifier,
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

    private function getReferenceEntityIdentifier($result): ReferenceEntityIdentifier
    {
        if (!isset($result['reference_entity_identifier'])) {
            throw new \LogicException('The record should have an reference entity identifier');
        }
        $normalizedReferenceEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['reference_entity_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return ReferenceEntityIdentifier::fromString($normalizedReferenceEntityIdentifier);
    }

    private function hydrateRecord($result): Record
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $valueKeyCollection = ($this->findValueKeyCollection)($referenceEntityIdentifier);
        $indexedAttributes = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);

        return $this->recordHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
    }
}

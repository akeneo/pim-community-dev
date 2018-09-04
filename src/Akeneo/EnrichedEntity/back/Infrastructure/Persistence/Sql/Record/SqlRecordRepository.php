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
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordHydrator;
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

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(
        Connection $sqlConnection,
        RecordHydratorInterface $recordHydrator
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->recordHydrator = $recordHydrator;
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
        INSERT INTO akeneo_enriched_entity_record (identifier, code, enriched_entity_identifier, labels, valueCollection)
        VALUES (:identifier, :code, :enriched_entity_identifier, :labels, :valueCollection);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $record->getIdentifier(),
                'code' => (string) $record->getCode(),
                'enriched_entity_identifier' => (string) $record->getEnrichedEntityIdentifier(),
                'labels' => $serializedLabels,
                'valueCollection' => '{}'
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one enriched entity, but %d rows were affected', $affectedRows)
            );
        }
    }

    /**
     */
    public function update(Record $record): void
    {
        $serializedLabels = $this->getSerializedLabels($record);
        $insert = <<<SQL
        UPDATE akeneo_enriched_entity_record
        SET labels = :labels, valueCollection = :valueCollection
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => $record->getIdentifier(),
                'labels' => $serializedLabels,
                'valueCollection' => '{}'
            ]
        );

        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to save one enriched entity, but %d rows were affected', $affectedRows)
            );
        }
    }

    public function getByEnrichedEntityAndCode(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): Record
    {
        $fetch = <<<SQL
        SELECT identifier, code, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
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

        return $this->hydrateRecord($result['identifier'], $result['code'], $result['enriched_entity_identifier'], $result['labels']);
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        $fetch = <<<SQL
        SELECT identifier, code, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'identifier' => (string) $identifier,
            ]
        );
        $result = $statement->fetch();

        if (!$result) {
            throw RecordNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateRecord($result['identifier'], $result['code'], $result['enriched_entity_identifier'], $result['labels']);
    }

    private function hydrateRecord(
        string $identifier,
        string $code,
        string $enrichedEntityIdentifier,
        string $normalizedLabels
    ): Record {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)
            ->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)
            ->convertToPHPValue($code, $platform);

        $record = Record::create(
            RecordIdentifier::fromString($identifier),
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            RecordCode::fromString($code),
            $labels,
            ValueCollection::fromValues([])
        );

        return $record;
    }

    private function getSerializedLabels(Record $record): string
    {
        $labels = [];
        foreach ($record->getLabelCodes() as $localeCode) {
            $labels[$localeCode] = $record->getLabel($localeCode);
        }

        return json_encode($labels);
    }

    public function nextIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): RecordIdentifier
    {
        return RecordIdentifier::create(
            (string) $enrichedEntityIdentifier,
            (string) $code,
            Uuid::uuid4()->toString()
        );
    }
}

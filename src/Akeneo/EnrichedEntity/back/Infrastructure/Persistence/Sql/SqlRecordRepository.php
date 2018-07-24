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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordRepository implements RecordRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
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
        INSERT INTO akeneo_enriched_entity_record (identifier, enriched_entity_identifier, labels, data)
        VALUES (:identifier, :enriched_entity_identifier, :labels, :data);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => $record->getIdentifier()->getIdentifier(),
                'enriched_entity_identifier' => $record->getIdentifier()->getEnrichedEntityIdentifier(),
                'labels' => $serializedLabels,
                'data' => '{}'
            ]
        );
        if ($affectedRows !== 1) {
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
        SET labels = :labels, data = :data
        WHERE identifier = :identifier AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => $record->getIdentifier()->getIdentifier(),
                'enriched_entity_identifier' => $record->getIdentifier()->getEnrichedEntityIdentifier(),
                'labels' => $serializedLabels,
                'data' => '{}'
            ]
        );

        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to save one enriched entity, but %d rows were affected', $affectedRows)
            );
        }
    }

    public function getByIdentifier(
        RecordIdentifier $identifier
    ): Record {
        $fetch = <<<SQL
        SELECT identifier, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
        WHERE identifier = :identifier
        AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'identifier' => $identifier->getIdentifier(),
                'enriched_entity_identifier' => $identifier->getEnrichedEntityIdentifier(),
            ]
        );
        $result = $statement->fetch();
        $statement->closeCursor();

        if (!$result) {
            throw RecordNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateRecord($result['identifier'], $result['enriched_entity_identifier'], $result['labels']);
    }

    private function hydrateRecord(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $normalizedLabels
    ): Record {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)
            ->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);

        $record = Record::create(
            RecordIdentifier::create($enrichedEntityIdentifier, $identifier),
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            RecordCode::fromString($identifier),
            $labels
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
}

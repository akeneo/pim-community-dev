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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EntityNotFoundException;
use Akeneo\EnrichedEntity\back\Domain\Repository\RecordRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordRepository implements RecordRepository
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

    /**
     * Depending on the database table state, the sql query "REPLACE INTO ... " might affect one row (the insert use
     * case) or two rows (the update use case)
     * @see https://dev.mysql.com/doc/refman/8.0/en/mysql-affected-rows.html
     *
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save(Record $record): void
    {
        $serializedLabels = $this->getSerializedLabels($record);
        $insert = <<<SQL
        REPLACE INTO akeneo_enriched_entity_record (identifier, enriched_entity_identifier, labels, data)
        VALUES (:identifier, :enriched_entity_identifier, :labels, :data);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $record->getIdentifier(),
                'enriched_entity_identifier' => (string) $record->getEnrichedEntityIdentifier(),
                'labels' => $serializedLabels,
                'data' => '{}'
            ]
        );

        if ($affectedRows === 0) {
            throw new \RuntimeException('Expected to save one enriched entity, but none was saved');
        }
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        $fetch = <<<SQL
        SELECT identifier, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['identifier' => (string) $identifier]
        );
        $result = $statement->fetch();
        $statement->closeCursor();

        if (!$result) {
            throw EntityNotFoundException::withIdentifier(Record::class, (string) $identifier);
        }

        return $this->hydrateRecord($result['identifier'], $result['enriched_entity_identifier'], $result['labels']);
    }

    public function all(): array
    {
        $selectAllQuery = <<<SQL
        SELECT identifier, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAll();
        $statement->closeCursor();

        $records = [];
        foreach ($results as $result) {
            $records[] = $this->hydrateRecord(
                $result['identifier'],
                $result['enriched_entity_identifier'],
                $result['labels']
            );
        }

        return $records;
    }

    private function hydrateRecord(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $normalizedLabels
    ): Record {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);

        $record = Record::create(
            RecordIdentifier::fromString($identifier),
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
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

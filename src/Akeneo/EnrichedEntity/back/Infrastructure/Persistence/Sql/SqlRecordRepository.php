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
use Akeneo\EnrichedEntity\Domain\Repository\EntityNotFoundException;
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

    public function getByIdentifier(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier
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
                'identifier' => (string) $identifier,
                'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
            ]
        );
        $result = $statement->fetch();
        $statement->closeCursor();

        if (!$result) {
            // TODO: Here we only give the Record identifier, it's not enough to identify it
            throw EntityNotFoundException::withIdentifier(Record::class, (string) $identifier);
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
            RecordIdentifier::fromString($enrichedEntityIdentifier, $identifier),
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier), RecordCode::fromString($identifier),
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

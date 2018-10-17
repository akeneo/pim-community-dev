<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindSearchableRecords
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byRecordIdentifier(RecordIdentifier $recordIdentifier): ?SearchableRecordItem
    {
        $sqlQuery = <<<SQL
        SELECT identifier, reference_entity_identifier, code, labels, value_collection
        FROM akeneo_reference_entity_record
        WHERE identifier = :identifier;
SQL;

        $statement = $this->connection->executeQuery($sqlQuery, ['identifier' => (string) $recordIdentifier]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return !$result ? null : $this->hydrateRecordToIndex(
            $result['identifier'],
            $result['reference_entity_identifier'],
            $result['code'],
            $result['labels'],
            $this->cleanValues($result['value_collection'])
        );
    }

    public function byReferenceEntityIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): \Iterator
    {
        $sqlQuery = <<<SQL
        SELECT identifier, reference_entity_identifier, code, labels, value_collection
        FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;

        $statement = $this->connection->executeQuery($sqlQuery, ['reference_entity_identifier' => (string) $referenceEntityIdentifier]);
        while (false !== $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $this->hydrateRecordToIndex(
                $result['identifier'],
                $result['reference_entity_identifier'],
                $result['code'],
                $result['labels'],
                $this->cleanValues($result['value_collection'])
            );
        }
    }

    private function hydrateRecordToIndex(
        string $identifier,
        string $referenceEntityIdentifier,
        string $code,
        string $normalizedLabels,
        array $values
    ): SearchableRecordItem {
        $platform = $this->connection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($referenceEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);

        $recordItem = new SearchableRecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $labels;
        $recordItem->values = $values;

        return $recordItem;
    }

    private function cleanValues(string $values): array
    {
        $cleanValues = strip_tags(html_entity_decode(str_replace(["\r", "\n"], ' ', $values)));

        return json_decode($cleanValues, true);
    }
}

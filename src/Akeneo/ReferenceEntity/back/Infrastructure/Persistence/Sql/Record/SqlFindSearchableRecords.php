<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindSearchableRecordsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchableRecordItem;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindSearchableRecords implements FindSearchableRecordsInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function byRecordIdentifier(RecordIdentifier $recordIdentifier): ?SearchableRecordItem
    {
        $sqlQuery = <<<SQL
        SELECT rec.identifier, rec.reference_entity_identifier, rec.code, rec.value_collection, ref.attribute_as_label, rec.updated_at
        FROM akeneo_reference_entity_record rec
        INNER JOIN akeneo_reference_entity_reference_entity ref ON ref.identifier = rec.reference_entity_identifier
        WHERE rec.identifier = :record_identifier;
SQL;

        $statement = $this->connection->executeQuery($sqlQuery, ['record_identifier' => (string) $recordIdentifier]);
        $result = $statement->fetchAssociative();

        return $result ? $this->hydrateRecordToIndex(
            $result['identifier'],
            $result['reference_entity_identifier'],
            $result['code'],
            $result['updated_at'],
            ValuesDecoder::decode($result['value_collection']),
            $result['attribute_as_label']
        ) : null;
    }

    public function byReferenceEntityIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): \Iterator
    {
        $sqlQuery = <<<SQL
        SELECT rec.identifier, rec.reference_entity_identifier, rec.code, rec.value_collection, ref.attribute_as_label, rec.updated_at
        FROM akeneo_reference_entity_record rec
        INNER JOIN akeneo_reference_entity_reference_entity ref ON ref.identifier = rec.reference_entity_identifier
        WHERE ref.identifier = :reference_entity_identifier;
SQL;

        $statement = $this->connection->executeQuery($sqlQuery, ['reference_entity_identifier' => (string) $referenceEntityIdentifier]);
        while (false !== $result = $statement->fetchAssociative()) {
            yield $this->hydrateRecordToIndex(
                $result['identifier'],
                $result['reference_entity_identifier'],
                $result['code'],
                $result['updated_at'],
                ValuesDecoder::decode($result['value_collection']),
                $result['attribute_as_label']
            );
        }
    }

    private function hydrateRecordToIndex(
        string $identifier,
        string $referenceEntityIdentifier,
        string $code,
        string $updatedAt,
        array $values,
        ?string $attributeAsLabel
    ): SearchableRecordItem {
        $platform = $this->connection->getDatabasePlatform();

        $identifier = Type::getType(Types::STRING)->convertToPHPValue($identifier, $platform);
        $referenceEntityIdentifier = Type::getType(Types::STRING)
            ->convertToPHPValue($referenceEntityIdentifier, $platform);
        $code = Type::getType(Types::STRING)->convertToPHPValue($code, $platform);
        $attributeAsLabel = Type::getType(Types::STRING)->convertToPHPValue($attributeAsLabel, $platform);
        $updatedAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($updatedAt, $platform);

        $recordItem = new SearchableRecordItem();
        $recordItem->identifier = $identifier;
        $recordItem->referenceEntityIdentifier = $referenceEntityIdentifier;
        $recordItem->code = $code;
        $recordItem->labels = $this->getLabels($attributeAsLabel, $values);
        $recordItem->values = $values;
        $recordItem->updatedAt = $updatedAt;

        return $recordItem;
    }

    private function getLabels(?string $attributeAsLabelIdentifier, array $values): array
    {
        if (null === $attributeAsLabelIdentifier) {
            return [];
        }

        return array_reduce(
            $values,
            function (array $labels, array $value) use ($attributeAsLabelIdentifier) {
                if ($value['attribute'] === $attributeAsLabelIdentifier) {
                    $labels[$value['locale']] = $value['data'];
                }

                return $labels;
            },
            []
        );
    }
}

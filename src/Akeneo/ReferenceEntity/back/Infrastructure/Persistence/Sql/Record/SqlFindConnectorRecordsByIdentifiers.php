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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordsByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ConnectorRecordHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorRecordsByIdentifiers implements FindConnectorRecordsByIdentifiersInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private ConnectorRecordHydrator $recordHydrator,
        private FindValueKeyCollectionInterface $findValueKeyCollection,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $identifiers, RecordQuery $recordQuery): array
    {
        $sql = <<<SQL
            SELECT 
                identifier,
                code,
                reference_entity_identifier,
                created_at,
                updated_at,
                value_collection
            FROM akeneo_reference_entity_record
            WHERE identifier IN (:identifiers)
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );
        $results = $statement->fetchAllAssociative();
        $orderedResults = $this->orderRecordItems($results, $identifiers);

        return empty($orderedResults) ? [] : $this->hydrateRecords($orderedResults, $recordQuery);
    }

    private function orderRecordItems(array $normalizedRecordItems, array $orderedIdentifiers): array
    {
        $resultIndexedByIdentifier = array_column($normalizedRecordItems, null, 'identifier');
        $resultIndexedByIdentifier = array_change_key_case($resultIndexedByIdentifier, CASE_LOWER);

        $existingIdentifiers = [];
        foreach ($orderedIdentifiers as $orderedIdentifier) {
            $satinizedIdentifier = trim(strtolower($orderedIdentifier));

            if (isset($resultIndexedByIdentifier[$satinizedIdentifier])) {
                $existingIdentifiers[$satinizedIdentifier] = $satinizedIdentifier;
            }
        }

        $result = array_replace($existingIdentifiers, $resultIndexedByIdentifier);

        return array_values($result);
    }

    /**
     * @return ConnectorRecord[]
     */
    private function hydrateRecords(array $results, RecordQuery $recordQuery): array
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier(current($results));
        $valueKeyCollection = $this->findValueKeyCollection->find($referenceEntityIdentifier);
        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find($referenceEntityIdentifier);

        $hydratedRecords = [];
        foreach ($results as $result) {
            $hydratedRecord = $this->recordHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
            $hydratedRecords[] = $this->filterRecordValues($hydratedRecord, $recordQuery);
        }

        return $hydratedRecords;
    }

    private function getReferenceEntityIdentifier($result): ReferenceEntityIdentifier
    {
        if (!isset($result['reference_entity_identifier'])) {
            throw new \LogicException('The record should have a reference entity identifier');
        }
        $normalizedReferenceEntityIdentifier = Type::getType(Types::STRING)->convertToPHPValue(
            $result['reference_entity_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return ReferenceEntityIdentifier::fromString($normalizedReferenceEntityIdentifier);
    }

    private function filterRecordValues(ConnectorRecord $connectorRecord, RecordQuery $recordQuery): ConnectorRecord
    {
        $channelReference = $recordQuery->getChannelReferenceValuesFilter();
        if (!$channelReference->isEmpty()) {
            $connectorRecord = $connectorRecord->getRecordWithValuesFilteredOnChannel($channelReference->getIdentifier());
        }

        $localesIdentifiers = $recordQuery->getLocaleIdentifiersValuesFilter();
        if (!$localesIdentifiers->isEmpty()) {
            $connectorRecord = $connectorRecord->getRecordWithValuesFilteredOnLocales($localesIdentifiers);
        }

        return $connectorRecord;
    }
}

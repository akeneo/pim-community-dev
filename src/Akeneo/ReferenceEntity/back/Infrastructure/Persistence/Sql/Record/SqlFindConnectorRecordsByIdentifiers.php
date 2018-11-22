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

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorRecordsByIdentifiers implements FindConnectorRecordsByIdentifiersInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var ConnectorRecordHydrator */
    private $recordHydrator;

    public function __construct(
        Connection $connection,
        ConnectorRecordHydrator $hydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->sqlConnection = $connection;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->recordHydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers, RecordQuery $recordQuery): array
    {
        $sql = <<<SQL
            SELECT 
                identifier, 
                code, 
                reference_entity_identifier, 
                labels, 
                value_collection,
                fi.file_key as image_file_key,
                fi.original_filename as image_original_filename
            FROM
                akeneo_reference_entity_record record
                LEFT JOIN akeneo_file_storage_file_info fi ON fi.file_key = record.image
            WHERE identifier IN (:identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return empty($results) ? [] : $this->hydrateRecords($results, $recordQuery);
    }

    /**
     * @return ConnectorRecord[]
     */
    private function hydrateRecords(array $results, RecordQuery $recordQuery): array
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier(current($results));
        $valueKeyCollection = ($this->findValueKeyCollection)($referenceEntityIdentifier);
        $indexedAttributes = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);

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
        $normalizedReferenceEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
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
            $connectorRecord = $connectorRecord->getRecordWithValuesAndLabelsFilteredOnLocales($localesIdentifiers);
        }

        return $connectorRecord;
    }
}

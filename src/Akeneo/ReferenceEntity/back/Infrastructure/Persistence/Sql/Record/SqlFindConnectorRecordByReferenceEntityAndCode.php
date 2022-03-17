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

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ConnectorRecordHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindConnectorRecordByReferenceEntityAndCode implements FindConnectorRecordByReferenceEntityAndCodeInterface
{
    public function __construct(
        private Connection $connection,
        private ConnectorRecordHydrator $recordHydrator,
        private FindValueKeyCollectionInterface $findValueKeyCollection,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?ConnectorRecord
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
            WHERE 
                code = :code AND reference_entity_identifier = :reference_entity_identifier;
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'code' => (string) $recordCode,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );
        $result = $statement->fetchAssociative();

        if (empty($result)) {
            return null;
        }

        return $this->hydrateRecord($result);
    }

    private function hydrateRecord(array $result): ConnectorRecord
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($referenceEntityIdentifier);
        $indexedAttributes = $this->findAttributesIndexedByIdentifier->find($referenceEntityIdentifier);

        return $this->recordHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
    }

    private function getReferenceEntityIdentifier($result): ReferenceEntityIdentifier
    {
        if (!isset($result['reference_entity_identifier'])) {
            throw new \LogicException('The record should have a reference entity identifier');
        }
        $normalizedReferenceEntityIdentifier = Type::getType(Types::STRING)->convertToPHPValue(
            $result['reference_entity_identifier'],
            $this->connection->getDatabasePlatform()
        );

        return ReferenceEntityIdentifier::fromString($normalizedReferenceEntityIdentifier);
    }
}

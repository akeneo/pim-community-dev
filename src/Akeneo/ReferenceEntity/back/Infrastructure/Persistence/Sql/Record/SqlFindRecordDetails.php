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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\GenerateEmptyValuesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordDetails implements FindRecordDetailsInterface
{
    private Connection $sqlConnection;
    private RecordDetailsHydratorInterface $recordDetailsHydrator;
    private GenerateEmptyValuesInterface $generateEmptyValues;
    private FindValueKeyCollectionInterface $findValueKeyCollection;
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    public function __construct(
        Connection $sqlConnection,
        RecordDetailsHydratorInterface $recordDetailsHydrator,
        GenerateEmptyValuesInterface $generateEmptyValues,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->recordDetailsHydrator = $recordDetailsHydrator;
        $this->generateEmptyValues = $generateEmptyValues;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?RecordDetails
    {
        $result = $this->fetchResult($referenceEntityIdentifier, $recordCode);

        if (empty($result)) {
            return null;
        }

        return $this->hydrateRecordDetails($result);
    }

    private function fetchResult(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): array
    {
        $query = <<<SQL
        SELECT
            record.identifier,
            record.code,
            record.reference_entity_identifier,
            record.value_collection,
            record.created_at,
            record.updated_at,
            reference.attribute_as_image,
            reference.attribute_as_label
        FROM akeneo_reference_entity_record AS record
        INNER JOIN akeneo_reference_entity_reference_entity AS reference
            ON reference.identifier = record.reference_entity_identifier
        WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => (string) $recordCode,
            'reference_entity_identifier' => (string) $referenceEntityIdentifier,
        ]);
        $result = $statement->fetchAssociative();
        $statement->free();

        return $result ?: [];
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

    private function hydrateRecordDetails($result): RecordDetails
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($referenceEntityIdentifier);
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($referenceEntityIdentifier);
        $emptyValues = $this->generateEmptyValues->generate($referenceEntityIdentifier);

        return $this->recordDetailsHydrator->hydrate(
            $result,
            $emptyValues,
            $valueKeyCollection,
            $attributesIndexedByIdentifier
        );
    }
}

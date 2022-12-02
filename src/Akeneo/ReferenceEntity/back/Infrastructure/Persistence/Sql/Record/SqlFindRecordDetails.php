<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
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
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindRecordDetails implements FindRecordDetailsInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private RecordDetailsHydratorInterface $recordDetailsHydrator,
        private GenerateEmptyValuesInterface $generateEmptyValues,
        private FindValueKeyCollectionInterface $findValueKeyCollection,
        private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?RecordDetails
    {
        $recordsDetails = $this->findByCodes($referenceEntityIdentifier, [$recordCode]);
        $recordCode = strtolower((string) $recordCode);
        $recordsDetails = array_change_key_case($recordsDetails);

        return $recordsDetails[$recordCode] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodes(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        return array_reduce(
            $this->fetchResults($referenceEntityIdentifier, $recordCodes),
            function (array $indexedRecordsDetails, array $normalizedRecordDetails) {
                $recordDetails = $this->hydrateRecordDetails($normalizedRecordDetails);
                $indexedRecordsDetails[(string) $recordDetails->code] = $recordDetails;

                return $indexedRecordsDetails;
            },
            [],
        );
    }

    private function fetchResults(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
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
        WHERE code IN (:codes) AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            [
                'codes' => $recordCodes,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ],
            [
                'codes' => Connection::PARAM_STR_ARRAY,
            ],
        );
        $result = $statement->fetchAllAssociative();
        $statement->free();

        return $result ?: [];
    }

    private function getReferenceEntityIdentifier(array $result): ReferenceEntityIdentifier
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

    private function hydrateRecordDetails(array $result): RecordDetails
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($referenceEntityIdentifier);
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($referenceEntityIdentifier);
        $emptyValues = $this->generateEmptyValues->generate($referenceEntityIdentifier);

        return $this->recordDetailsHydrator->hydrate(
            $result,
            $emptyValues,
            $valueKeyCollection,
            $attributesIndexedByIdentifier,
        );
    }
}

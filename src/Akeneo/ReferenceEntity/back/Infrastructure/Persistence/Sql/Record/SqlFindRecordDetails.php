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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\GenerateEmptyValuesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordDetails implements FindRecordDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var RecordDetailsHydratorInterface */
    private $recordDetailsHydrator;

    /** @var GenerateEmptyValuesInterface */
    private $generateEmptyValues;

    public function __construct(
        Connection $sqlConnection,
        RecordDetailsHydratorInterface $recordDetailsHydrator,
        GenerateEmptyValuesInterface $generateEmptyValues
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->recordDetailsHydrator = $recordDetailsHydrator;
        $this->generateEmptyValues = $generateEmptyValues;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?RecordDetails
    {
        $result = $this->fetchResult($referenceEntityIdentifier, $recordCode);

        if (empty($result)) {
            return null;
        }

        $recordDetails = $this->hydrateRecordDetails($result);

        return $recordDetails;
    }

    private function fetchResult(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.code, ee.reference_entity_identifier, ee.labels, ee.value_collection, fi.image
        FROM akeneo_reference_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE code = :code && reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => (string) $recordCode,
            'reference_entity_identifier' => (string) $referenceEntityIdentifier,
        ]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    private function getReferenceEntityIdentifier($result): ReferenceEntityIdentifier
    {
        if (!isset($result['reference_entity_identifier'])) {
            throw new \LogicException('The record should have an reference entity identifier');
        }
        $normalizedReferenceEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['reference_entity_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return ReferenceEntityIdentifier::fromString($normalizedReferenceEntityIdentifier);
    }

    private function hydrateRecordDetails($result): RecordDetails
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $emptyValues = ($this->generateEmptyValues)($referenceEntityIdentifier);

        return $this->recordDetailsHydrator->hydrate($result, $emptyValues);
    }
}

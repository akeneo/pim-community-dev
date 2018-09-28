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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\GenerateEmptyValuesInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordDetailsHydratorInterface;
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
    public function __invoke(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $recordCode): ?RecordDetails
    {
        $result = $this->fetchResult($enrichedEntityIdentifier, $recordCode);

        if (empty($result)) {
            return null;
        }

        $recordDetails = $this->hydrateRecordDetails($result);

        return $recordDetails;
    }

    private function fetchResult(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $recordCode): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.code, ee.enriched_entity_identifier, ee.labels, ee.value_collection, fi.image
        FROM akeneo_enriched_entity_record AS ee
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = ee.image
        WHERE code = :code && enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'code' => (string) $recordCode,
            'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
        ]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    private function getEnrichedEntityIdentifier($result): EnrichedEntityIdentifier
    {
        if (!isset($result['enriched_entity_identifier'])) {
            throw new \LogicException('The record should have an enriched entity identifier');
        }
        $normalizedEnrichedEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['enriched_entity_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return EnrichedEntityIdentifier::fromString($normalizedEnrichedEntityIdentifier);
    }

    private function hydrateRecordDetails($result): RecordDetails
    {
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifier($result);
        $emptyValues = ($this->generateEmptyValues)($enrichedEntityIdentifier);

        return $this->recordDetailsHydrator->hydrate($result, $emptyValues);
    }
}

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
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordForConnectorByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordForConnectorHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordForConnectorByReferenceEntityAndCode implements FindRecordForConnectorByReferenceEntityAndCodeInterface
{
    /** @var Connection */
    private $connection;

    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var RecordForConnectorHydrator */
    private $recordHydrator;

    public function __construct(
        Connection $connection,
        RecordForConnectorHydrator $hydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->connection = $connection;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->recordHydrator = $hydrator;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?RecordForConnector
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
                akeneo_reference_entity_record ee
                LEFT JOIN akeneo_file_storage_file_info fi ON fi.file_key = ee.image
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
        $result = $statement->fetch();

        if (empty($result)) {
            return null;
        }

        return $this->hydrateRecord($result);
    }

    private function hydrateRecord(array $result): RecordForConnector
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $valueKeyCollection = ($this->findValueKeyCollection)($referenceEntityIdentifier);
        $indexedAttributes = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);

        return $this->recordHydrator->hydrate($result, $valueKeyCollection, $indexedAttributes);
    }

    private function getReferenceEntityIdentifier($result): ReferenceEntityIdentifier
    {
        if (!isset($result['reference_entity_identifier'])) {
            throw new \LogicException('The record should have a reference entity identifier');
        }
        $normalizedReferenceEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['reference_entity_identifier'],
            $this->connection->getDatabasePlatform()
        );

        return ReferenceEntityIdentifier::fromString($normalizedReferenceEntityIdentifier);
    }
}

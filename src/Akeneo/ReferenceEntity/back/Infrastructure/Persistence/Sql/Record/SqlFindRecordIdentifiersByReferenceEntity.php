<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordIdentifiersByReferenceEntityInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlFindRecordIdentifiersByReferenceEntity implements FindRecordIdentifiersByReferenceEntityInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): Iterable
    {
        $statement = $this->connection->executeQuery(
            'SELECT identifier FROM akeneo_reference_entity_record WHERE reference_entity_identifier = :referenceEntityIdentifier',
            ['referenceEntityIdentifier' => (string)$referenceEntityIdentifier]
        );

        $platform = $this->connection->getDatabasePlatform();
        while (false !== $identifier = $statement->fetchColumn()) {
            $stringIdentifier = Type::getType(Types::STRING)->convertToPHPValue($identifier, $platform);

            yield RecordIdentifier::fromString($stringIdentifier);
        }
    }
}

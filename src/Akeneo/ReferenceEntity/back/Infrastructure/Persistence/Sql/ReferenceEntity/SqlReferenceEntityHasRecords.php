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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityHasRecordsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlReferenceEntityHasRecords implements ReferenceEntityHasRecordsInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function hasRecords(ReferenceEntityIdentifier $identifier): bool
    {
        $statement = $this->executeQuery($identifier);

        return $this->doesReferenceEntityHaveRecords($statement);
    }

    private function executeQuery(ReferenceEntityIdentifier $referenceEntityIdentifier): Result
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_record
            WHERE reference_entity_identifier = :reference_entity_identifier
        ) as has_records
SQL;

        return $this->sqlConnection->executeQuery($query, [
            'reference_entity_identifier' => $referenceEntityIdentifier,
        ]);
    }

    private function doesReferenceEntityHaveRecords(Result $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['has_records'], $platform);
    }
}

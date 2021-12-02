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
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlReferenceEntityExists implements ReferenceEntityExistsInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function withIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        $statement = $this->executeQuery($referenceEntityIdentifier);

        return $this->isIdentifierExisting($statement);
    }

    private function executeQuery(ReferenceEntityIdentifier $referenceEntityIdentifier): Statement
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_reference_entity
            WHERE identifier = :identifier 
        ) as is_existing
SQL;

        return $this->sqlConnection->executeQuery($query, ['identifier' => (string) $referenceEntityIdentifier]);
    }

    private function isIdentifierExisting(Statement $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}

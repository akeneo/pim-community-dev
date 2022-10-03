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
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordExists implements RecordExistsInterface
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function withIdentifier(RecordIdentifier $recordIdentifier): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_record
            WHERE identifier = :identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $recordIdentifier
        ]);

        return $this->isIdentifierExisting($statement);
    }

    public function withReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $code): bool
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_reference_entity_record
            WHERE reference_entity_identifier = :referenceEntityIdentifier
            AND code = :code
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
            'code' => (string) $code
        ]);

        return $this->isIdentifierExisting($statement);
    }

    private function isIdentifierExisting(Result $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}

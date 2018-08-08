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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\ExistsRecordInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use PDO;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlExistsRecord implements ExistsRecordInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withIdentifier(RecordIdentifier $recordIdentifier): bool
    {
        $statement = $this->executeQuery($recordIdentifier);

        return $this->isIdentifierExisting($statement);
    }

    private function executeQuery(RecordIdentifier $recordIdentifier): Statement
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_enriched_entity_record
            WHERE identifier = :identifier AND enriched_entity_identifier = :enriched_entity_identifier
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier'                 => $recordIdentifier->getIdentifier(),
            'enriched_entity_identifier' => $recordIdentifier->getEnrichedEntityIdentifier(),
        ]);

        return $statement;
    }

    private function isIdentifierExisting(Statement $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $isExisting = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);

        return $isExisting;
    }
}

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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityHasRecordsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use PDO;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlEnrichedEntityHasRecords implements EnrichedEntityHasRecordsInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(EnrichedEntityIdentifier $identifier): bool
    {
        $statement = $this->executeQuery($identifier);

        return $this->doesEnrichedEntityHaveRecords($statement);
    }

    private function executeQuery(EnrichedEntityIdentifier $enrichedEntityIdentifier): Statement
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_enriched_entity_record
            WHERE enriched_entity_identifier = :enriched_entity_identifier
        ) as has_records
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
        ]);

        return $statement;
    }

    private function doesEnrichedEntityHaveRecords(Statement $statement): bool
    {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $hasRecords = Type::getType(Type::BOOLEAN)->convertToPhpValue($result['has_records'], $platform);

        return $hasRecords;
    }
}

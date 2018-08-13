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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use PDO;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlEnrichedEntityExists implements EnrichedEntityExistsInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function withIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier): bool
    {
        $statement = $this->executeQuery($enrichedEntityIdentifier);

        return $this->isIdentifierExisting($statement);
    }

    private function executeQuery(EnrichedEntityIdentifier $enrichedEntityIdentifier): Statement
    {
        $query = <<<SQL
        SELECT EXISTS (
            SELECT 1
            FROM akeneo_enriched_entity_enriched_entity
            WHERE identifier = :identifier 
        ) as is_existing
SQL;
        $statement = $this->sqlConnection->executeQuery($query, ['identifier' => (string) $enrichedEntityIdentifier]);

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

<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Query\SqlFindAttributeNextOrderInterface;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Doctrine\DBAL\Connection;

class SqlFindAttributeNextOrder implements SqlFindAttributeNextOrderInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function forEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): int
    {
        $query = <<<SQL
        SELECT MAX(attribute_order)
        FROM akeneo_enriched_entity_attribute
        WHERE enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
        ]);
        $result = $statement->fetch();
        $statement->closeCursor();

        return !$result ? 0 : $result;
    }
}

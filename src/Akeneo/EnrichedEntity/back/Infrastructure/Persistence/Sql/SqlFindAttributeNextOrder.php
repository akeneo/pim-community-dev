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
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Doctrine\DBAL\Connection;

class SqlFindAttributeNextOrder implements FindAttributeNextOrderInterface
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

    public function withEnrichedEntityIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier): int
    {
        $query = <<<SQL
        SELECT MAX(attribute_order)
        FROM akeneo_enriched_entity_attribute
        WHERE enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
        ]);
        $result = $statement->fetchColumn();
        $statement->closeCursor();

        return null === $result ? 0 : (intval($result) + 1);
    }
}

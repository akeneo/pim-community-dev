<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsReferenceEntityLinkedToATableColumn;
use Doctrine\DBAL\Connection;

final class SqlIsReferenceEntityLinkedToATableColumn implements IsReferenceEntityLinkedToATableColumn
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forIdentifier(string $identifier): bool
    {
        $query = <<<SQL
            SELECT EXISTS(
                SELECT id FROM pim_catalog_table_column
                INNER JOIN akeneo_reference_entity_reference_entity
                    ON identifier = :identifier
                    AND properties->"$.reference_entity_identifier" = identifier
            )
        SQL;

        return (bool) $this->connection->executeQuery(
            $query,
            ['identifier' => $identifier]
        )->fetchOne();
    }
}

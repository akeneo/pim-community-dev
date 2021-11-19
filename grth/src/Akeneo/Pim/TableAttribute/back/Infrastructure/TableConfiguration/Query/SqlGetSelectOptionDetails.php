<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetSelectOptionDetails;
use Doctrine\DBAL\Connection;

class SqlGetSelectOptionDetails implements GetSelectOptionDetails
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): \Iterator
    {
        $sql = <<<SQL
        SELECT
            CONCAT(attribute.code, '-', column_definition.code, '-', option_definition.code) as search_after,
            attribute.code as attribute_code,
            column_definition.id as column_id,
            column_definition.code as column_code,
            option_definition.code as option_code,
            option_definition.labels as labels
        FROM pim_catalog_attribute attribute
            INNER JOIN pim_catalog_table_column column_definition ON attribute.id = column_definition.attribute_id
            INNER JOIN pim_catalog_table_column_select_option option_definition ON column_definition.id = option_definition.column_id
        HAVING search_after > :searchAfter
        ORDER BY search_after
        LIMIT 1000;
        SQL;

        $searchAfter = '';
        while (null !== $searchAfter) {
            $rows = $this->connection->executeQuery($sql, ['searchAfter' => $searchAfter])->fetchAllAssociative();
            if ([] === $rows) {
                $searchAfter = null;
            }
            foreach ($rows as $row) {
                $searchAfter = $row['search_after'];
                yield new SelectOptionDetails(
                    $row['attribute_code'],
                    $row['column_code'],
                    $row['option_code'],
                    \json_decode($row['labels'], true)
                );
            }
        }
    }
}

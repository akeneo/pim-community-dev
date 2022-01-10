<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetColumnsLinkedToAReferenceEntity;
use Doctrine\DBAL\Connection;

final class SqlGetColumnsLinkedToAReferenceEntity implements GetColumnsLinkedToAReferenceEntity
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array<array<string, string>>
     *     example: [["attribute_code" => "nutrition", "column_code" => "brand"], ...]
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function forIdentifier(string $referenceEntityIdentifier): array
    {
        $query = <<<SQL
            SELECT pca.code AS attribute_code, c.code AS column_code
            FROM pim_catalog_table_column AS c
                JOIN pim_catalog_attribute pca ON c.attribute_id = pca.id
                JOIN akeneo_reference_entity_reference_entity AS e
                    ON e.identifier = :identifier
                    AND c.properties->"$.reference_entity_identifier" = e.identifier
        SQL;

        return $this->connection->executeQuery(
            $query,
            ['identifier' => $referenceEntityIdentifier]
        )->fetchAllAssociative();
    }
}

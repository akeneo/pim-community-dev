<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByAttributeOptionQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogsByAttributeOptionQuery implements GetCatalogsByAttributeOptionQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritDoc}
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function execute(AttributeOptionInterface $attributeOption): array
    {
        $query = <<<SQL
            SELECT
                BIN_TO_UUID(catalog.id) AS id,
                catalog.name,
                oro_user.username AS owner_username
            FROM akeneo_catalog catalog
            JOIN oro_user ON oro_user.id = catalog.owner_id
            WHERE catalog.id IN (
                SELECT DISTINCT sub.id
                FROM akeneo_catalog AS sub,
                     JSON_TABLE(sub.product_selection_criteria, '$[*]' COLUMNS (
                         field VARCHAR(255)  PATH '$.field',
                         value json PATH '$.value')
                    ) AS criterion
                WHERE criterion.field = :attributeCode AND JSON_CONTAINS(criterion.value, json_quote(:attributeOptionCode), '$')
                AND sub.is_enabled
            )
        SQL;

        /** @var array<array{id: string, name: string, owner_username: string, is_enabled: string}> $rows */
        $rows = $this->connection->executeQuery($query, [
            'attributeCode' => $attributeOption->getAttribute()->getCode(),
            'attributeOptionCode' => $attributeOption->getCode(),
        ])->fetchAllAssociative();

        return \array_map(static fn ($row): Catalog => new Catalog(
            $row['id'],
            $row['name'],
            $row['owner_username'],
            true,
        ), $rows);
    }
}

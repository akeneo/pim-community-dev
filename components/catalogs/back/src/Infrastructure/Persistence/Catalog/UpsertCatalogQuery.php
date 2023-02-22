<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpsertCatalogQuery implements UpsertCatalogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(Catalog $catalog): void
    {
        $query = <<<SQL
        INSERT INTO akeneo_catalog (
            id,
            name,
            owner_id,
            is_enabled,
            product_selection_criteria,
            product_value_filters,
            product_mapping
        )
        VALUES (
            UUID_TO_BIN(:id),
            :name,
            (SELECT id FROM oro_user WHERE username = :owner_username LIMIT 1),
            :is_enabled,
            :product_selection_criteria,
            :product_value_filters,
            :product_mapping
        )
        ON DUPLICATE KEY UPDATE
            name = :name,
            is_enabled = :is_enabled,
            product_selection_criteria = :product_selection_criteria,
            product_value_filters = :product_value_filters,
            product_mapping = :product_mapping,
            updated = NOW()
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => $catalog->getId(),
                'name' => $catalog->getName(),
                'owner_username' => $catalog->getOwnerUsername(),
                'is_enabled' => $catalog->isEnabled(),
                'product_selection_criteria' => \array_values($catalog->getProductSelectionCriteria()),
                'product_value_filters' => $catalog->getProductValueFilters(),
                'product_mapping' => $catalog->getProductMapping(),
            ],
            [
                'is_enabled' => Types::BOOLEAN,
                'product_selection_criteria' => Types::JSON,
                'product_value_filters' => Types::JSON,
                'product_mapping' => Types::JSON,
            ],
        );
    }
}

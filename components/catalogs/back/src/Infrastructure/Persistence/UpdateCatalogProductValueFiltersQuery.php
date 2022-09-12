<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductValueFiltersQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogProductValueFiltersQuery implements UpdateCatalogProductValueFiltersQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $id, array $productValueFilters): void
    {
        $query = <<<SQL
        UPDATE akeneo_catalog catalog
        SET product_value_filters = :product_value_filters
        WHERE catalog.id = :id
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'product_value_filters' => $productValueFilters,
            ],
            [
                'product_value_filters' => Types::JSON,
            ]
        );
    }
}

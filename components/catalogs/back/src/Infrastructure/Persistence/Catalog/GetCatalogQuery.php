<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Domain\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog\Catalog;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type DatabaseCatalog array{
 *      id: string,
 *      name: string,
 *      owner_username: string,
 *      is_enabled: string,
 *      product_selection_criteria: string,
 *      product_value_filters: string,
 * }
 *
 * @phpstan-import-type ProductSelectionCriterion from Catalog
 * @phpstan-import-type ProductValueFilters from Catalog
 */
final class GetCatalogQuery implements GetCatalogQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(string $id): Catalog
    {
        $query = <<<SQL
        SELECT
            BIN_TO_UUID(catalog.id) AS id,
            catalog.name,
            catalog.is_enabled,
            oro_user.username AS owner_username,
            catalog.product_selection_criteria,
            catalog.product_value_filters
        FROM akeneo_catalog catalog
        JOIN oro_user ON oro_user.id = catalog.owner_id
        WHERE catalog.id = :id
        SQL;

        /** @var DatabaseCatalog|false $row */
        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchAssociative();

        if (!$row) {
            throw new \LogicException('Catalog not found.');
        }

        /** @var array<array-key, ProductSelectionCriterion> $productSelectionCriteria */
        $productSelectionCriteria = \json_decode($row['product_selection_criteria'], true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($productSelectionCriteria)) {
            throw new \LogicException('Invalid JSON in product_selection_criteria column');
        }

        /** @var ProductValueFilters $filters */
        $productValueFilters = \json_decode($row['product_value_filters'], true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($productValueFilters)) {
            throw new \LogicException('Invalid JSON in product_value_filters column');
        }

        return new Catalog(
            $row['id'],
            $row['name'],
            $row['owner_username'],
            (bool) $row['is_enabled'],
            $productSelectionCriteria,
            $productValueFilters,
        );
    }
}

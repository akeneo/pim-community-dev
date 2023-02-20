<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
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
 *      product_mapping: string,
 * }
 *
 * @phpstan-import-type ProductSelectionCriteria from Catalog
 * @phpstan-import-type ProductValueFilters from Catalog
 * @phpstan-import-type ProductMapping from Catalog
 */
final class GetCatalogQuery implements GetCatalogQueryInterface
{
    public function __construct(
        private Connection $connection,
        private GetProductMappingSchemaQueryInterface $getProductMappingSchemaQuery,
    ) {
    }

    public function execute(string $catalogId): Catalog
    {
        $query = <<<SQL
        SELECT
            BIN_TO_UUID(catalog.id) AS id,
            catalog.name,
            catalog.is_enabled,
            oro_user.username AS owner_username,
            catalog.product_selection_criteria,
            catalog.product_value_filters,
            catalog.product_mapping
        FROM akeneo_catalog catalog
        JOIN oro_user ON oro_user.id = catalog.owner_id
        WHERE catalog.id = :id
        SQL;

        /** @var DatabaseCatalog|false $row */
        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($catalogId)->getBytes(),
        ])->fetchAssociative();

        if (!$row) {
            throw new CatalogNotFoundException();
        }

        /** @var ProductSelectionCriteria|null $productSelectionCriteria */
        $productSelectionCriteria = \json_decode($row['product_selection_criteria'], true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($productSelectionCriteria)) {
            throw new \LogicException('Invalid JSON in product_selection_criteria column');
        }

        /** @var ProductValueFilters|null $productValueFilters */
        $productValueFilters = \json_decode($row['product_value_filters'], true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($productValueFilters)) {
            throw new \LogicException('Invalid JSON in product_value_filters column');
        }

        /** @var ProductMapping|null $productMapping */
        $productMapping = \json_decode($row['product_mapping'], true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($productMapping)) {
            throw new \LogicException('Invalid JSON in product_mapping column');
        }

        $productMapping = $this->reorderProductMapping($productMapping, $row['id']);

        return new Catalog(
            $row['id'],
            $row['name'],
            $row['owner_username'],
            (bool) $row['is_enabled'],
            $productSelectionCriteria,
            $productValueFilters,
            $productMapping,
        );
    }

    /**
     * @param ProductMapping $productMapping
     *
     * @return ProductMapping
     */
    private function reorderProductMapping(array $productMapping, string $catalogId): array
    {
        if ([] == $productMapping) {
            return [];
        }

        $productMappingSchema = $this->getProductMappingSchemaQuery->execute($catalogId);

        /** @var string[] $orderedKeys */
        $orderedKeys = \array_keys($productMappingSchema['properties']);

        /** @var ProductMapping $orderedProductmapping */
        $orderedProductmapping = \array_merge(\array_flip($orderedKeys), $productMapping);

        return $orderedProductmapping;
    }
}

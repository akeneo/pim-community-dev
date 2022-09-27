<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogOwnerIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogProductValueFiltersQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductsQueryInterface
 * @phpstan-import-type ProductValue from GetProductsQueryInterface
 */
class GetProductsQuery implements GetProductsQueryInterface
{
    private const PROPERTIES = [
      'uuid',
      'enabled',
      'family',
      'categories',
      'groups',
      'parent',
      'values',
      'associations',
      'quantified_associations',
      'created',
      'updated',
    ];

    public function __construct(
        private GetProductUuidsQueryInterface $getProductUuidsQuery,
        private GetConnectorProducts $getConnectorProducts,
        private GetCatalogProductValueFiltersQueryInterface $getCatalogProductValueFiltersQuery,
        private GetCatalogOwnerIdQueryInterface $getCatalogOwnerIdQuery,
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        string $catalogId,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array {
        $filters = $this->getCatalogProductValueFiltersQuery->execute($catalogId);

        $uuids = $this->getProductUuidsQuery->execute($catalogId, $searchAfter, $limit, $updatedAfter, $updatedBefore);

        $connectorProducts = $this->getConnectorProducts->fromProductUuids(
            \array_map(static fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids),
            $this->getCatalogOwnerIdQuery->execute($catalogId),
            null,
            null,
            $filters['locales'] ?? null,
        );

        /** @var array<Product> $products */
        $products = $this->connectorProductWithUuidNormalizer->normalizeConnectorProductList($connectorProducts);
        $products = $this->filterNormalizedProperties($products, self::PROPERTIES);
        $products = $this->filterChannels($products, $filters['channels'] ?? null);

        return $this->filterCurrencies($products, $filters['currencies'] ?? null);
    }

    /**
     * @param array<Product> $products
     * @param array<string>|null $channels
     * @return array<Product>
     */
    private function filterChannels(array $products, ?array $channels = null): array
    {
        if (null === $channels || \count($channels) === 0) {
            return $products;
        }

        foreach ($products as &$product) {
            foreach ($product['values'] as &$values) {
                $values = \array_values(\array_filter(
                    $values,
                    static fn (array $value) => $value['scope'] === null || \in_array($value['scope'], $channels)
                ));
            }
        }

        return $products;
    }

    /**
     * @param array<Product> $products
     * @param array<string>|null $currencies
     * @return array<Product>
     */
    private function filterCurrencies(array $products, ?array $currencies = null): array
    {
        if (null === $currencies || \count($currencies) === 0) {
            return $products;
        }

        foreach ($products as &$product) {
            foreach ($product['values'] as &$values) {
                /** @var ProductValue $value */
                foreach ($values as &$value) {
                    if (!\is_array($value['data']) || !isset($value['data'][0]['currency'])) {
                        break;
                    }

                    $value['data'] = \array_values(
                        \array_filter(
                            (array) $value['data'],
                            static fn (array $price) => \in_array($price['currency'], $currencies)
                        )
                    );
                }
            }
        }

        return $products;
    }

    /**
     * @param array<Product> $products
     * @param array<string> $whitelist
     * @return array<Product>
     */
    private function filterNormalizedProperties(array $products, array $whitelist): array
    {
        $keys = \array_flip($whitelist);

        foreach ($products as &$product) {
            $product = \array_intersect_key($product, $keys);
        }

        return $products;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsWithFilteredValuesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductsWithFilteredValuesQueryInterface
 * @phpstan-import-type ProductValue from GetProductsWithFilteredValuesQueryInterface
 *
 * @phpstan-type ProductValueFilters array{
 *      channels?: array<string>|null,
 *      locales?: array<string>|null,
 *      currencies?: array<string>|null,
 * }
 */
class GetProductsWithFilteredValuesQuery implements GetProductsWithFilteredValuesQueryInterface
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
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
        private GetUserIdFromUsernameQueryInterface $getUserIdFromUsernameQuery,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array {
        $uuids = $this->getProductUuidsQuery->execute($catalog, $searchAfter, $limit, $updatedAfter, $updatedBefore);

        $filters = $catalog->getProductValueFilters();

        $connectorProducts = $this->getConnectorProducts->fromProductUuids(
            \array_map(static fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids),
            $this->getUserIdFromUsernameQuery->execute($catalog->getOwnerUsername()),
            null,
            null,
            isset($filters['locales']) && !empty($filters['locales']) ? $filters['locales'] : null,
        );

        $products = $this->connectorProductWithUuidNormalizer->normalizeConnectorProductList($connectorProducts);

        /**
         * If values is empty the normalizer return an object instead of an array, so we cast it to be consistent
         * @psalm-suppress MixedAssignment
         * @psalm-suppress MixedArrayAssignment
         * @psalm-suppress MixedArrayAccess
         */
        foreach ($products as &$product) {
            $product['values'] = (array) $product['values'];
        }

        /**
         * @var array<Product> $products
         * @var array<Product> $productsWithFilteredValues
         */
        $productsWithFilteredValues = $this->filterNormalizedProperties($products, self::PROPERTIES);
        $productsWithFilteredValues = $this->filterChannels($productsWithFilteredValues, $filters['channels'] ?? null);

        return $this->filterCurrencies($productsWithFilteredValues, $filters['currencies'] ?? null);
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
                    static fn (array $value): bool => $value['scope'] === null || \in_array($value['scope'], $channels),
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
                            $value['data'],
                            static fn (array $price): bool => \in_array($price['currency'], $currencies),
                        ),
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

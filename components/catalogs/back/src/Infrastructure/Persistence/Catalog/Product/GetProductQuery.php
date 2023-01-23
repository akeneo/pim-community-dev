<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\User\GetUserIdFromUsernameQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductQueryInterface
 * @phpstan-import-type ProductValue from GetProductQueryInterface
 */
class GetProductQuery implements GetProductQueryInterface
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
        private GetUserIdFromUsernameQueryInterface $getUserIdFromUsernameQuery,
        private GetConnectorProducts $getConnectorProducts,
        private ConnectorProductWithUuidNormalizer $connectorProductWithUuidNormalizer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Catalog $catalog, string $productUuid): array
    {
        try {
            $connectorProduct = $this->getConnectorProducts->fromProductUuid(
                Uuid::fromString($productUuid),
                $this->getUserIdFromUsernameQuery->execute($catalog->getOwnerUsername()),
            );
        } catch (ObjectNotFoundException $notFoundException) {
            throw new ProductNotFoundException(previous: $notFoundException);
        }

        $valueFilters = $catalog->getProductValueFilters();

        /** @var Product $product */
        $product = $this->connectorProductWithUuidNormalizer->normalizeConnectorProduct($connectorProduct);
        $product = $this->filterNormalizedProperties($product, self::PROPERTIES);
        $product = $this->filterChannels($product, $valueFilters['channels'] ?? null);
        $product = $this->filterLocales($product, $valueFilters['locales'] ?? null);

        return $this->filterCurrencies($product, $valueFilters['currencies'] ?? null);
    }

    /**
     * @param Product $product
     * @param array<string> $whitelist
     * @return Product
     */
    private function filterNormalizedProperties(array $product, array $whitelist): array
    {
        /** @var Product $normalizedProduct */
        $normalizedProduct = \array_intersect_key($product, \array_flip($whitelist));
        return $normalizedProduct;
    }

    /**
     * @param Product $product
     * @param array<string>|null $channels
     * @return Product
     */
    private function filterChannels(array $product, ?array $channels = null): array
    {
        if (null === $channels || \count($channels) === 0) {
            return $product;
        }

        foreach ($product['values'] as &$values) {
            $values = \array_values(\array_filter(
                $values,
                static fn (array $value) => $value['scope'] === null || \in_array($value['scope'], $channels, true),
            ));
        }

        return $product;
    }

    /**
     * @param Product $product
     * @param array<string>|null $locales
     * @return Product
     */
    private function filterLocales(array $product, ?array $locales = null): array
    {
        if (null === $locales || \count($locales) === 0) {
            return $product;
        }

        foreach ($product['values'] as &$values) {
            $values = \array_values(\array_filter(
                $values,
                static fn (array $value) => $value['locale'] === null || \in_array($value['locale'], $locales, true),
            ));
        }

        return $product;
    }

    /**
     * @param Product $product
     * @param array<string>|null $currencies
     * @return Product
     */
    private function filterCurrencies(array $product, ?array $currencies = null): array
    {
        if (null === $currencies || \count($currencies) === 0) {
            return $product;
        }

        foreach ($product['values'] as &$values) {
            /** @var ProductValue $value */
            foreach ($values as &$value) {
                if (!\is_array($value['data']) || !isset($value['data'][0]['currency'])) {
                    break;
                }

                $value['data'] = \array_values(
                    \array_filter(
                        $value['data'],
                        static fn (array $price) => \in_array($price['currency'], $currencies, true),
                    ),
                );
            }
        }

        return $product;
    }
}

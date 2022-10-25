<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Product\GetProductsQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type Product from GetProductsQueryInterface
 */
final class GetMappedProductsHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
        private GetProductsQueryInterface $getProductsQuery,
    ) {
    }

    /**
     * @return array<array-key, MappedProduct>
     */
    public function __invoke(GetMappedProductsQuery $query): array
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        if (!$catalog->isEnabled()) {
            throw new CatalogDisabledException();
        }

        $products = $this->getProductsQuery->execute(
            $catalog,
            $query->getSearchAfter(),
            $query->getLimit(),
            $query->getUpdatedAfter(),
            $query->getUpdatedBefore(),
        );

        $productMappingSchemaFile = \sprintf('%s_product.json', $catalog->getId());

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            throw new ServiceApiProductSchemaMappingNotFoundException();
        }

        $productMappingSchemaRaw = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile)
        );

        if (false === $productMappingSchemaRaw) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        $productMappingSchema = \json_decode($productMappingSchemaRaw, false, 512, JSON_THROW_ON_ERROR);
        $productMapping = $catalog->getProductMapping();

        return \array_map(
            /** @var Product $product */
            static function (array $product) use ($productMappingSchema, $productMapping): array {
                $mappedProduct = [];

                /** @var string $key */
                foreach ($productMappingSchema->properties as $key => $property) {
                    $sourceValue = '';
                    if (\array_key_exists($key, $productMapping)) {
                        $sourceValue = (string) $product->getValue(
                            $productMapping[$key]['source'],
                            $productMapping[$key]['locale'],
                            $productMapping[$key]['scope']
                        )?->getData() ?: '';
                    }
                    $mappedProduct[$key] = $sourceValue;
                }

                return $mappedProduct;
            },
            $products
        );
    }

    /**
     * @param Product $product
     */
    private function getProductValue(array $product, ?string $attributeCode, ?string $locale, ?string $scope): string
    {
//        return ;
    }
}

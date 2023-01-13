<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;
use Opis\JsonSchema\Validator;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 */
final class MappedProductCollector
{
    /**
     * @var array<string, array<MappedProduct>>
     */
    private array $collected = [];

    public function __construct(
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param MappedProduct $product
     */
    public function collect(string $catalogId, array $product): void
    {
        $this->collected[$catalogId][] = $product;
    }

    public function analyze(): void
    {
        // @todo add a feature flag to disable the analyze, it can be costly in perfs

        $validator = new Validator();
        $resolver = $validator->resolver();
        \assert(null !== $resolver);

        foreach ($this->collected as $catalogId => $products) {
            $schema = $this->getProductMappingSchema($catalogId);

            foreach ($products as $product) {
                $product = \json_decode(json_encode($product), false, 512, JSON_THROW_ON_ERROR);

                $result = $validator->validate($product, $schema);

                if ($result->hasError()) {
                    $this->logger->warning('A mapped product does not match its catalog schema', [
                        'product' => $product->{"uuid"},
                        'catalog' => $catalogId,
                    ]);
                }
            }
        }

        $this->collected = [];
    }

    // @todo move to a query in persistence
    private function getProductMappingSchema(string $catalogId): object
    {
        $productMappingSchemaFile = \sprintf('%s_product.json', $catalogId);

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            throw new ProductSchemaMappingNotFoundException();
        }

        $productMappingSchemaRaw = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile)
        );

        if (false === $productMappingSchemaRaw) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        $productMappingSchema = \json_decode($productMappingSchemaRaw, false, 512, JSON_THROW_ON_ERROR);

        return $productMappingSchema;
    }
}

<?php

namespace Akeneo\Test\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductsQueryInterface;

class InMemoryFindNonExistingProductsQuery implements FindNonExistingProductsQueryInterface
{
    private InMemoryProductRepository $productRepository;

    public function __construct(
        InMemoryProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        $existingIdentifiers = $this->getAllProductIdentifiers();

        $nonExistingProductIdentifiers = array_values(array_diff($productIdentifiers, $existingIdentifiers));

        return $nonExistingProductIdentifiers;
    }

    public function byProductUuids(array $productUuids): array
    {
        $existingUuids = $this->getAllProductUuids();

        $nonExistingProductUuids = array_values(array_diff($productUuids, $existingUuids));

        return $nonExistingProductUuids;
    }

    private function getAllProductIdentifiers(): array
    {
        $products = $this->productRepository->findAll();

        return array_map(function (ProductInterface $product) {
            return $product->getIdentifier();
        }, $products);
    }

    private function getAllProductUuids(): array
    {
        $products = $this->productRepository->findAll();

        return array_map(function (ProductInterface $product) {
            return $product->getUuid()->toString();
        }, $products);
    }
}

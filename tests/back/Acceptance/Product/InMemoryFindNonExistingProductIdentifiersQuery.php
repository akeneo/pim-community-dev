<?php

namespace Akeneo\Test\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductIdentifiersQueryInterface;

class InMemoryFindNonExistingProductIdentifiersQuery implements FindNonExistingProductIdentifiersQueryInterface
{
    /** @var InMemoryProductRepository */
    private $productRepository;

    public function __construct(
        InMemoryProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function execute(array $productIdentifiers): array
    {
        $existingIdentifiers = $this->getAllProductIdentifiers();

        $nonExistingProductIdentifiers = array_values(array_diff($productIdentifiers, $existingIdentifiers));

        return $nonExistingProductIdentifiers;
    }

    private function getAllProductIdentifiers(): array
    {
        $products = $this->productRepository->findAll();

        return array_map(function (ProductInterface $product) {
            return $product->getIdentifier();
        }, $products);
    }
}

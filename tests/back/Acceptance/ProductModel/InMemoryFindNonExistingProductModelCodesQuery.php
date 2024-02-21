<?php

namespace Akeneo\Test\Acceptance\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductModelCodesQueryInterface;

class InMemoryFindNonExistingProductModelCodesQuery implements FindNonExistingProductModelCodesQueryInterface
{
    /** @var InMemoryProductModelRepository */
    private $productModelRepository;

    public function __construct(
        InMemoryProductModelRepository $productModelRepository
    ) {
        $this->productModelRepository = $productModelRepository;
    }

    public function execute(array $productModelCodes): array
    {
        $existingCodes = $this->getAllProductModelCodes();

        $nonExistingProductModelCodes = array_values(array_diff($productModelCodes, $existingCodes));

        return $nonExistingProductModelCodes;
    }

    private function getAllProductModelCodes(): array
    {
        $productModels = $this->productModelRepository->findAll();

        return array_map(function (ProductModelInterface $productModel) {
            return $productModel->getCode();
        }, $productModels);
    }
}

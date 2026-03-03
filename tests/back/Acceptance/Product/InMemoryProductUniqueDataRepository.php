<?php

declare(strict_types=1);


namespace AkeneoTest\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryProductUniqueDataRepository implements ProductUniqueDataRepositoryInterface
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uniqueDataExistsInAnotherProduct(ValueInterface $value, ProductInterface $product): bool
    {
        foreach ($this->productRepository->findAll() as $productInRepo) {
            if ($product->getUuid()->equals($productInRepo->getUuid())) {
                continue;
            }
            if ($productInRepo->getValues()->getSame($value)?->isEqual($value)) {
                return true;
            }
        }

        return false;
    }
}

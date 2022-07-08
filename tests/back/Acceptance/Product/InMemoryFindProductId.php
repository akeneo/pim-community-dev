<?php

declare(strict_types=1);

namespace AkeneoTest\Acceptance\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindProductId implements FindId
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    public function fromIdentifier(string $identifier): null|string
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        return $product ? (string)$product->getUuid() : null;
    }
}

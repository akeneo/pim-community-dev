<?php

declare(strict_types=1);

namespace AkeneoTest\Acceptance\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindProductModelId implements FindId
{
    public function __construct(private ProductModelRepositoryInterface $productModelRepository)
    {
    }

    public function fromIdentifier(string $identifier): null|string
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($identifier);

        return $productModel ? (string) $productModel->getId() : null;
    }
}

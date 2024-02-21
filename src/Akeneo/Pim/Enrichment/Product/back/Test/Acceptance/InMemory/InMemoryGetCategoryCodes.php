<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetCategoryCodes implements GetCategoryCodes
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductUuids(array $productUuids): array
    {
        $results = [];
        $productUuidsAsString = \array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $productUuids);
        foreach ($this->productRepository->findAll() as $product) {
            if (\in_array(\strtolower($product->getUuid()->toString()), $productUuidsAsString)) {
                $results[$product->getUuid()->toString()] = $product->getCategoryCodes();
            }
        }

        return $results;
    }
}

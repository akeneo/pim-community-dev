<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;

/**
 * InMemory implementation of the SelectProductIdentifierValuesQuery.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemorySelectProductIdentifierValuesQuery implements SelectProductIdentifierValuesQueryInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        InMemoryProductRepository $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->productRepository = $productRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $productIds): ProductIdentifierValuesCollection
    {
        $result = new ProductIdentifierValuesCollection();
        $mapping = $this->identifiersMappingRepository->find();
        if ($mapping->isEmpty()) {
            return $result;
        }

        foreach ($productIds as $productId) {
            $product = $this->productRepository->find($productId->toInt());
            if (null === $product) {
                continue;
            }
            $mappedIdentifiers = [];

            foreach ($mapping as $franklinCode => $identifierMapping) {
                $mappedAttributeCode = $identifierMapping->getAttributeCode();
                if (null === $mappedAttributeCode) {
                    continue;
                }
                $value = $product->getValue((string) $mappedAttributeCode);
                if (null !== $value && $value->hasData()) {
                    $mappedIdentifiers[$franklinCode] = (string) $value->getData();
                }
            }

            $result->add(new ProductIdentifierValues($productId, $mappedIdentifiers));
        }

        return $result;
    }
}

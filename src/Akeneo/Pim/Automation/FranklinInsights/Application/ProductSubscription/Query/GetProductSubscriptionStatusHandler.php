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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

/**
 * Handles a GetProductSubscriptionStatus query and returns a ProductSubscriptionStatus read model.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusHandler
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param GetConnectionStatusHandler $getConnectionStatusHandler
     * @param ProductRepositoryInterface $productRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        GetConnectionStatusHandler $getConnectionStatusHandler,
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
        $this->productRepository = $productRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param GetProductSubscriptionStatusQuery $query
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductSubscriptionStatus
     */
    public function handle(GetProductSubscriptionStatusQuery $query): ProductSubscriptionStatus
    {
        $product = $this->productRepository->find($query->getProductId());
        if (null === $product) {
            throw new \InvalidArgumentException(sprintf('There is no product with id "%s"', $query->getProductId()));
        }
        $productSubscription = $this->productSubscriptionRepository->findOneByProductId($query->getProductId());
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery());

        return new ProductSubscriptionStatus(
            $connectionStatus,
            $productSubscription instanceof ProductSubscription,
            null !== $product->getFamily(),
            $this->isMappingFilled($product),
            $product->isVariant()
        );
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    private function isMappingFilled(ProductInterface $product): bool
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        foreach ($identifiersMapping->getMapping() as $identifierMapping) {
            $pimAttributeCode = $identifierMapping->getAttribute();
            if (null !== $pimAttributeCode &&
                null !== $product->getValue($pimAttributeCode->getCode()) &&
                null !== $product->getValue($pimAttributeCode->getCode())->getData()
            ) {
                return true;
            }
        }

        return false;
    }
}

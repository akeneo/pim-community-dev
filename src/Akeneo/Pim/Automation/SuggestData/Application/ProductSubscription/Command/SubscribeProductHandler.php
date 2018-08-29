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

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

/**
 * Handles a SubscribeProduct command.
 *
 * It checks that the product exists and creates the product subscription
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscribeProductHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->dataProviderFactory = $dataProviderFactory;
    }

    /**
     * @param SubscribeProductCommand $command
     *
     * @throws ProductSubscriptionException
     */
    public function handle(SubscribeProductCommand $command): void
    {
        $product = $this->validateProduct($command->getProductId());

        $this->subscribe($product);
    }

    /**
     * Creates a subscription request, sends it to the data provider and saves the resulting subscription
     *
     * @param ProductInterface $product
     */
    private function subscribe(ProductInterface $product): void
    {
        $subscriptionRequest = new ProductSubscriptionRequest($product);
        $dataProvider = $this->dataProviderFactory->create();

        $subscriptionResponse = $dataProvider->subscribe($subscriptionRequest);
        $subscription = new ProductSubscription($product, $subscriptionResponse->getSubscriptionId());
        $subscription->setSuggestedData($subscriptionResponse->getSuggestedData());

        $this->productSubscriptionRepository->save($subscription);
    }

    /**
     * @param int $productId
     *
     * @return ProductInterface
     */
    private function validateProduct(int $productId): ProductInterface
    {
        $product = $this->productRepository->find($productId);
        if (null === $product) {
            throw new ProductSubscriptionException(
                sprintf('Could not find product with id "%d"', $productId)
            );
        }
        if (null === $product->getFamily()) {
            throw new ProductSubscriptionException(sprintf('Cannot subscribe a product without family'));
        }

        $productSubscription = $this->productSubscriptionRepository->findOneByProductId($productId);
        if (null !== $productSubscription) {
            throw new ProductSubscriptionException(
                sprintf('The product with id "%d" is already subscribed', $productId)
            );
        }

        return $product;
    }
}

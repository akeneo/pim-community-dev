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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
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

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->productRepository = $productRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
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
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw new ProductSubscriptionException('Identifiers mapping has no identifier defined');
        }

        $product = $this->productRepository->find($command->getProductId());
        if (null === $product) {
            throw new ProductSubscriptionException(
                sprintf('Could not find product with id "%d"', $command->getProductId())
            );
        }
        if (null === $product->getFamily()) {
            throw new ProductSubscriptionException(sprintf('Cannot subscribe a product without family'));
        }

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
        try {
            $subscriptionResponse = $dataProvider->subscribe($subscriptionRequest);
        } catch (\Exception $e) {
            throw new ProductSubscriptionException($e->getMessage());
        }

        $subscription = $this->findOrCreateSubscription(
            $subscriptionResponse->getProduct(),
            $subscriptionResponse->getSubscriptionId()
        );
        $subscription->setSuggestedData($subscriptionResponse->getSuggestedData());

        $this->productSubscriptionRepository->save($subscription);
    }

    /**
     * @param ProductInterface $product
     * @param string $subscriptionId
     *
     * @return ProductSubscriptionInterface
     */
    private function findOrCreateSubscription(
        ProductInterface $product,
        string $subscriptionId
    ): ProductSubscriptionInterface {
        $subscription = $this->productSubscriptionRepository->findOneByProductAndSubscriptionId(
            $product,
            $subscriptionId
        );
        if (null === $subscription) {
            $subscription = new ProductSubscription($product, $subscriptionId);
        }

        return $subscription;
    }
}

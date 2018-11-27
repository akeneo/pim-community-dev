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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;
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

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var CreateProposalHandler */
    private $createProposalHandler;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param CreateProposalHandler $createProposalHandler
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        CreateProposalHandler $createProposalHandler
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->createProposalHandler = $createProposalHandler;
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
     * Creates a subscription request, sends it to the data provider and saves the resulting subscription.
     *
     * @param ProductInterface $product
     *
     * @throws ProductSubscriptionException
     */
    private function subscribe(ProductInterface $product): void
    {
        $subscriptionRequest = new ProductSubscriptionRequest($product);

        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw new ProductSubscriptionException('No mapping defined');
        }

        $subscriptionResponse = $this->subscriptionProvider->subscribe($subscriptionRequest);
        $subscription = new ProductSubscription(
            $product->getId(),
            $subscriptionResponse->getSubscriptionId(),
            $subscriptionRequest->getMappedValues($identifiersMapping)
        );
        $suggestedData = new SuggestedData($subscriptionResponse->getSuggestedData());
        $subscription->setSuggestedData($suggestedData);
        $subscription->markAsMissingMapping($subscriptionResponse->isMappingMissing());

        $this->createProposalHandler->handle(new CreateProposalCommand($subscription));

        $this->productSubscriptionRepository->save($subscription);
    }

    /**
     * @param int $productId
     *
     * @throws ProductSubscriptionException
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
            throw ProductSubscriptionException::familyRequired();
        }

        $productSubscription = $this->productSubscriptionRepository->findOneByProductId($productId);
        if (null !== $productSubscription) {
            throw new ProductSubscriptionException(
                sprintf('The product with id "%d" is already subscribed', $productId)
            );
        }

        // TODO: check that product is not variant

        return $product;
    }
}

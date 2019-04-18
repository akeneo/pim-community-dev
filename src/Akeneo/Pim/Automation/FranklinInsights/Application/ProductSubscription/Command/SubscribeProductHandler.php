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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles a SubscribeProduct command.
 *
 * It checks that the product exists and creates the product subscription
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscribeProductHandler
{
    /** @var SelectProductInfosForSubscriptionQueryInterface */
    private $selectProductInfosForSubscriptionQuery;

    /** @var GetProductSubscriptionStatusHandler */
    private $getProductSubscriptionStatusHandler;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var CreateProposalHandler */
    private $createProposalHandler;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        CreateProposalHandler $createProposalHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->selectProductInfosForSubscriptionQuery = $selectProductInfosForSubscriptionQuery;
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->createProposalHandler = $createProposalHandler;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param SubscribeProductCommand $command
     *
     * @throws \InvalidArgumentException
     * @throws ProductSubscriptionException
     */
    public function handle(SubscribeProductCommand $command): void
    {
        $this->validateProduct($command->getProductId());

        $this->subscribe($command->getProductId());
    }

    /**
     * Creates a subscription request, sends it to the data provider and saves the resulting subscription.
     *
     * @param ProductId $productId
     *
     * @throws ProductSubscriptionException
     */
    private function subscribe(ProductId $productId): void
    {
        $productInfos = $this->selectProductInfosForSubscriptionQuery->execute($productId);

        $subscriptionRequest = new ProductSubscriptionRequest(
            $productInfos->getProductId(),
            $productInfos->getFamily(),
            $productInfos->getProductIdentifierValues(),
            $productInfos->getIdentifier()
        );

        $subscriptionResponse = $this->subscriptionProvider->subscribe($subscriptionRequest);

        $subscription = new ProductSubscription(
            $productInfos->getProductId(),
            $subscriptionResponse->getSubscriptionId(),
            $subscriptionRequest->getMappedValues()
        );
        $suggestedData = new SuggestedData($subscriptionResponse->getSuggestedData());
        $subscription->setSuggestedData($suggestedData);
        $subscription->markAsMissingMapping($subscriptionResponse->isMappingMissing());

        $this->productSubscriptionRepository->save($subscription);

        $this->createProposalHandler->handle(new CreateProposalCommand($subscription));

        $this->eventDispatcher->dispatch(ProductSubscribed::EVENT_NAME, new ProductSubscribed($subscription));
    }

    private function validateProduct(ProductId $productId): void
    {
        $productSubscriptionStatus = $this->getProductSubscriptionStatusHandler->handle(
            new GetProductSubscriptionStatusQuery($productId)
        );

        $productSubscriptionStatus->validate();
    }
}

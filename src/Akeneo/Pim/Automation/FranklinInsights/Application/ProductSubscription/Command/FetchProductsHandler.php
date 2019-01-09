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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;

/**
 * Handles a FetchProducts command.
 *
 * It fetches ProductSubscription from Franklin
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class FetchProductsHandler
{
    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        SubscriptionProviderInterface $subscriptionProvider,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository
    ) {
        $this->subscriptionProvider = $subscriptionProvider;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * @param FetchProductsCommand $command
     */
    public function handle(FetchProductsCommand $command): void
    {
        // TODO: Calculate last date (from command or fetch from repository) APAI-170

        foreach ($this->subscriptionProvider->fetch() as $subscriptionResponse) {
            $subscription = $this->productSubscriptionRepository->findOneByProductId(
                $subscriptionResponse->getProductId()
            );
            if (null === $subscription) {
                continue;
            }
            if (true === $subscriptionResponse->isCancelled()) {
                $this->productSubscriptionRepository->delete($subscription);

                continue;
            }

            $suggestedData = new SuggestedData($subscriptionResponse->getSuggestedData());
            $subscription->setSuggestedData($suggestedData);
            $subscription->markAsMissingMapping($subscriptionResponse->isMappingMissing());
            $this->productSubscriptionRepository->save($subscription);
        }
    }
}

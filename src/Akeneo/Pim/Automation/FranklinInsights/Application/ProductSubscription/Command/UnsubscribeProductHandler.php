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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

/**
 * Handles an UnsubscribeProduct command.
 *
 * It checks that the product is subscribed and unsubscribe it
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class UnsubscribeProductHandler
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository,
     * @param SubscriptionProviderInterface $subscriptionProvider
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionProvider = $subscriptionProvider;
    }

    /**
     * @param UnsubscribeProductCommand $command
     *
     * @throws ProductNotSubscribedException If product is not subscribed
     */
    public function handle(UnsubscribeProductCommand $command): void
    {
        $subscription = $this->subscriptionRepository->findOneByProductId($command->getProductId());
        if (null === $subscription) {
            throw ProductNotSubscribedException::notSubscribed($command->getProductId());
        }

        $this->subscriptionProvider->unsubscribe($subscription->getSubscriptionId());

        $this->subscriptionRepository->delete($subscription);
    }
}

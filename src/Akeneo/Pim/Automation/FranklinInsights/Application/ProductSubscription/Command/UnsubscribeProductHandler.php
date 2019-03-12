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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductUnsubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository,
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->eventDispatcher = $eventDispatcher;
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

        $this->eventDispatcher->dispatch(
            ProductUnsubscribed::EVENT_NAME,
            new ProductUnsubscribed($command->getProductId())
        );
    }
}

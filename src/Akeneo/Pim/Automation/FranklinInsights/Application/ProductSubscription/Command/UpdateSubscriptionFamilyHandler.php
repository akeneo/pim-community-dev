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

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UpdateSubscriptionFamilyHandler
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /**
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param SubscriptionProviderInterface $subscriptionProvider
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider
    ) {
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->subscriptionProvider = $subscriptionProvider;
    }

    /**
     * @param UpdateSubscriptionFamilyCommand $command
     */
    public function handle(UpdateSubscriptionFamilyCommand $command): void
    {
        $subscription = $this->productSubscriptionRepository->findOneByProductId($command->productId());
        if (null === $subscription) {
            return;
        }

        $this->subscriptionProvider->updateFamilyInfos($subscription->getSubscriptionId(), $command->family());
        // TODO: empty suggested_data? misses_mapping? dispatch an event?
    }
}

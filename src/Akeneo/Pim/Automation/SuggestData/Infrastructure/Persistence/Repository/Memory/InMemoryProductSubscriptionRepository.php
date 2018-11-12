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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var ProductSubscription[] */
    private $subscriptions = [];

    /**
     * {@inheritdoc}
     */
    public function save(ProductSubscription $subscription): void
    {
        $this->subscriptions[$subscription->getSubscriptionId()] = $subscription;
        ksort($this->subscriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductId(int $productId): ?ProductSubscription
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getProduct()->getId() === $productId) {
                return $subscription;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingSubscriptions(int $limit, ?string $searchAfter): array
    {
        $subscriptions = array_filter(
            array_values($this->subscriptions),
            function (ProductSubscription $subscription) use ($searchAfter) {
                if ($subscription->getSuggestedData()->isEmpty()) {
                    return false;
                }
                if (null !== $searchAfter && $subscription->getSubscriptionId() <= $searchAfter) {
                    return false;
                }

                return true;
            }
        );

        return array_slice($subscriptions, 0, $limit);
    }

    /**
     * @param ProductSubscription $subscription
     */
    public function delete(ProductSubscription $subscription): void
    {
        unset($this->subscriptions[$subscription->getSubscriptionId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedData(array $productIds): void
    {
        foreach ($this->subscriptions as $subscription) {
            if (in_array($subscription->getProduct()->getId(), $productIds)) {
                $subscription->setSuggestedData(new SuggestedData(null));
            }
        }
    }
}

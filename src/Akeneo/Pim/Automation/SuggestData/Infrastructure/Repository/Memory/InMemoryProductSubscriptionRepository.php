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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
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
        $productId = $subscription->getProduct()->getId();
        $this->subscriptions[$productId] = $subscription;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductId(int $productId): ?ProductSubscription
    {
        if (!isset($this->subscriptions[$productId])) {
            return null;
        }

        return $this->subscriptions[$productId];
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingSubscriptions(): array
    {
        return array_values(
            array_filter(
                $this->subscriptions,
                function (ProductSubscription $subscription) {
                    return !$subscription->getSuggestedData()->isEmpty();
                }
            )
        );
    }

    /**
     * @param ProductSubscription $subscription
     */
    public function delete(ProductSubscription $subscription): void
    {
        unset($this->subscriptions[$subscription->getProduct()->getId()]);
    }
}

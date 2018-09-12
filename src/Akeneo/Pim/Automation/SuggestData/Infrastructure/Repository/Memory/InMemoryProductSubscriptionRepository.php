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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var ProductSubscription[] */
    private $subscriptions = [];

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function findOneByProductAndSubscriptionId(
        ProductInterface $product,
        string $subscriptionId
    ): ?ProductSubscription {
        if (!isset($this->subscriptions[$product->getId()])) {
            return null;
        }

        $subscription = $this->subscriptions[$product->getId()];
        if ($subscriptionId !== $subscription->getSubscriptionId()) {
            return null;
        }

        return $subscription;
    }

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
     * {@inheritdoc}
     * @deprecated
     */
    public function getSubscriptionStatusForProductId(int $productId): array
    {
        if (!isset($this->subscriptions[$productId])) {
            return ['subscription_id' => ''];
        }

        return ['subscription_id' => $this->subscriptions[$productId]->getSubscriptionId()];
    }

    /**
     * @param ProductSubscription $subscription
     */
    public function delete(ProductSubscription $subscription): void
    {
        unset($this->subscriptions[$subscription->getProduct()->getId()]);
    }
}

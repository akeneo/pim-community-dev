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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Acceptance\Repository;

use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var array */
    private $subscriptions = [];

    /**
     * @param ProductSubscriptionInterface[] $subscriptions
     */
    public function __construct(array $subscriptions = [])
    {
        foreach ($subscriptions as $subscription) {
            $this->save($subscription);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductAndSubscriptionId(
        ProductInterface $product,
        string $subscriptionId
    ): ?ProductSubscriptionInterface {
        return $this->subscriptions[$product->getId()][$subscriptionId] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProductSubscriptionInterface $subscription): void
    {
        $productId = $subscription->getProduct()->getId();
        $subscriptionId = $subscription->getSubscriptionId();
        $this->subscriptions[$productId][$subscriptionId] = $subscription;
    }
}

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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Repository;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface ProductSubscriptionRepositoryInterface
{
    /**
     * @param ProductInterface $product
     * @param string $subscriptionId
     *
     * @return ProductSubscriptionInterface|null
     */
    public function findOneByProductAndSubscriptionId(
        ProductInterface $product,
        string $subscriptionId
    ): ?ProductSubscriptionInterface;

    /**
     * @param ProductSubscriptionInterface $subscription
     */
    public function save(ProductSubscriptionInterface $subscription): void;

    /**
     * @param int $productId
     *
     * @return ProductSubscriptionInterface|null
     */
    public function findOneByProductId(int $productId): ?ProductSubscriptionInterface;

    /**
     * @return ProductSubscriptionInterface[]
     */
    public function findPendingSubscriptions(): array;
}

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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface ProductSubscriptionRepositoryInterface
{
    /**
     * @param ProductSubscription $subscription
     */
    public function save(ProductSubscription $subscription): void;

    /**
     * @param int $productId
     *
     * @return ProductSubscription|null
     */
    public function findOneByProductId(int $productId): ?ProductSubscription;

    /**
     * @param int $limit
     * @param string|null $searchAfter
     *
     * @return ProductSubscription[]
     */
    public function findPendingSubscriptions(int $limit, ?string $searchAfter): array;

    /**
     * @param ProductSubscription $subscription
     */
    public function delete(ProductSubscription $subscription): void;
}

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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;

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
     * @param array $subscriptions
     */
    public function bulkSave(array $subscriptions): void;

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

    /**
     * @param array $subscriptions
     */
    public function bulkDelete(array $subscriptions): void;

    /**
     * Empty all suggested data.
     */
    public function emptySuggestedData(): void;

    /**
     * @param int[] $productIds
     */
    public function emptySuggestedDataByProducts(array $productIds): void;

    /**
     * @param string $familyCode
     */
    public function emptySuggestedDataAndMissingMappingByFamily(string $familyCode): void;

    /**
     * @return int
     */
    public function count(): int;
}

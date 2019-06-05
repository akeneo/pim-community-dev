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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
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
     * @param ProductSubscription[] $subscriptions
     */
    public function bulkSave(array $subscriptions): void;

    /**
     * @param ProductId $productId
     *
     * @return ProductSubscription|null
     */
    public function findOneByProductId(ProductId $productId): ?ProductSubscription;

    /**
     * @param ProductId[] $productIds
     *
     * @return ProductSubscription[]
     */
    public function findByProductIds(array $productIds): array;

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
     * @param ProductSubscription[] $subscriptions
     */
    public function bulkDelete(array $subscriptions): void;

    /**
     * Empty all suggested data.
     */
    public function emptySuggestedData(): void;

    /**
     * @param ProductId[] $productIds
     */
    public function emptySuggestedDataByProducts(array $productIds): void;

    /**
     * @param FamilyCode $familyCode
     */
    public function emptySuggestedDataAndMissingMappingByFamily(FamilyCode $familyCode): void;

    /**
     * @return int
     */
    public function count(): int;
}

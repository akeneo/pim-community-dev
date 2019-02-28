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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var ProductSubscription[] */
    private $subscriptions = [];

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProductSubscription $subscription): void
    {
        $this->subscriptions[(string) $subscription->getSubscriptionId()] = $subscription;
        ksort($this->subscriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductId(int $productId): ?ProductSubscription
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getProductId() === $productId) {
                return $subscription;
            }
        }

        return null;
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    public function findByProductIds(array $productIds): array
    {
        return array_filter($this->subscriptions, function (ProductSubscription $subscription) use ($productIds) {
            return in_array($subscription->getProductId(), $productIds, true);
        });
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
                if (null !== $searchAfter && (string) $subscription->getSubscriptionId() <= $searchAfter) {
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
        unset($this->subscriptions[(string) $subscription->getSubscriptionId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedData(): void
    {
        foreach ($this->subscriptions as $subscription) {
            $subscription->setSuggestedData(new SuggestedData([]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedDataByProducts(array $productIds): void
    {
        foreach ($this->subscriptions as $subscription) {
            if (in_array($subscription->getProductId(), $productIds)) {
                $subscription->setSuggestedData(new SuggestedData([]));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedDataAndMissingMappingByFamily(string $familyCode): void
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);

        foreach ($this->subscriptions as $subscription) {
            $product = $this->productRepository->find($subscription->getProductId());
            if ($product->getFamily()->getId() === $family->getId()) {
                $subscription->setSuggestedData(new SuggestedData([]));
                $subscription->markAsMissingMapping(true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->subscriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkSave(array $subscriptions): void
    {
        foreach ($subscriptions as $subscription) {
            $this->save($subscription);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bulkDelete(array $subscriptions): void
    {
        foreach ($subscriptions as $subscription) {
            $this->delete($subscription);
        }
    }
}

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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepository implements ProductSubscriptionRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProductSubscription $subscription): void
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function bulkSave(array $subscriptions): void
    {
        foreach ($subscriptions as $subscription) {
            if ($subscription instanceof ProductSubscription) {
                $this->em->persist($subscription);
            }
        }
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductId(int $productId): ?ProductSubscription
    {
        return $this->em->getRepository(ProductSubscription::class)->findOneByProductId($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingSubscriptions(int $limit, ?string $searchAfter): array
    {
        $qb = $this->em->createQueryBuilder()->select('subscription')->from(ProductSubscription::class, 'subscription');
        $qb->where(
            $qb->expr()->isNotNull('subscription.rawSuggestedData')
        );
        if (null !== $searchAfter) {
            $qb->andWhere('subscription.subscriptionId > :searchAfter')
               ->setParameter('searchAfter', $searchAfter);
        }
        $qb->addOrderBy('subscription.subscriptionId', 'ASC')
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ProductSubscription $subscription): void
    {
        $this->em->remove($subscription);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function bulkDelete(array $subscriptions): void
    {
        foreach ($subscriptions as $subscription) {
            if ($subscription instanceof ProductSubscription) {
                $this->em->remove($subscription);
            }
        }

        $this->em->flush();
        $this->em->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedData(): void
    {
        $query = <<<SQL
UPDATE pim_franklin_insights_product_subscription
SET raw_suggested_data = NULL
WHERE raw_suggested_data IS NOT NULL;
SQL;
        $this->em->getConnection()->executeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedDataByProducts(array $productIds): void
    {
        if (empty($productIds)) {
            return;
        }

        $query = <<<SQL
UPDATE pim_franklin_insights_product_subscription
SET raw_suggested_data = NULL 
WHERE raw_suggested_data IS NOT NULL
AND product_id IN (:productIds);
SQL;
        $this->em->getConnection()->executeQuery(
            $query,
            ['productIds' => $productIds],
            ['productIds' => Connection::PARAM_STR_ARRAY]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emptySuggestedDataAndMissingMappingByFamily(string $familyCode): void
    {
        $query = <<<SQL
UPDATE pim_franklin_insights_product_subscription s
INNER JOIN pim_catalog_product p ON p.id = s.product_id
INNER JOIN pim_catalog_family f ON f.id = p.family_id
SET s.raw_suggested_data = NULL, s.misses_mapping = false
WHERE s.raw_suggested_data IS NOT NULL
AND f.code = :familyCode;
SQL;
        $this->em->getConnection()->executeQuery($query, ['familyCode' => $familyCode]);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $sql = <<<SQL
SELECT COUNT(1) AS product_subscription_count from pim_franklin_insights_product_subscription;
SQL;
        $statement = $this->em->getConnection()->executeQuery($sql);
        $result = $statement->fetch();

        return intval($result['product_subscription_count']);
    }
}

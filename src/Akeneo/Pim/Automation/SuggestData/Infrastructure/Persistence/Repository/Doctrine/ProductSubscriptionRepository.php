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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
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
    public function findOneByProductId(int $productId): ?ProductSubscription
    {
        return $this->em->getRepository(ProductSubscription::class)->findOneByProduct($productId);
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
    public function emptySuggestedData(array $productIds): void
    {
        if (empty($productIds)) {
            return;
        }

        $qb = $this->em->createQueryBuilder();
        $qb->update(ProductSubscription::class, 'subscription')
           ->set('subscription.rawSuggestedData', ':rawSuggestedData')
           ->where(
               $qb->expr()->in('subscription.product', ':productIds')
           )
           ->setParameter('rawSuggestedData', null)
           ->setParameter('productIds', $productIds);

        $qb->getQuery()->execute();
    }
}

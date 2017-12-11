<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Pim\Component\Api\Repository\PageableRepositoryInterface;
use Pim\Component\Api\Repository\SearchAfterPageableRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface, PageableRepositoryInterface, SearchAfterPageableRepositoryInterface
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        string $className,
        AssetRepositoryInterface $assetRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?AssetInterface
    {
        return $this->assetRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return $this->assetRepository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterOffset(array $criteria, array $orders, $limit, $offset)
    {
        $qb = $this->createQueryBuilder('r');

        foreach ($criteria as $field => $criterion) {
            $qb->andWhere($qb->expr()->eq(sprintf('r.%s', $field), $qb->expr()->literal($criterion)));
        }

        foreach ($orders as $field => $sort) {
            $qb->addOrderBy(sprintf('r.%s', $field), $sort);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterIdentifier(array $criteria, array $orders, int $limit, array $from = [])
    {
        $qb = $this->createQueryBuilder('r');

        foreach ($criteria as $field => $criterion) {
            $qb->andWhere($qb->expr()->eq(sprintf('r.%s', $field), $qb->expr()->literal($criterion)));
        }

        foreach ($orders as $field => $sort) {
            $qb->addOrderBy(sprintf('r.%s', $field), $sort);
        }

        foreach ($from as $field => $value) {
            if ('ASC' === $orders[$field]) {
                $qb->andWhere($qb->expr()->gt(sprintf('r.%s', $field), $qb->expr()->literal($from[$field])));
            } else {
                $qb->andWhere($qb->expr()->lt(sprintf('r.%s', $field), $qb->expr()->literal($from[$field])));
            }
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }


    /**
     * {@inheritdoc}
     */
    public function count(array $criteria = [])
    {
        try {
            $qb = $this->createQueryBuilder('r');

            foreach ($criteria as $field => $criterion) {
                $qb->andWhere($qb->expr()->eq(sprintf('r.%s', $field), $qb->expr()->literal($criterion)));
            }

            return (int) $qb
                ->select('COUNT(r.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (UnexpectedResultException $e) {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        return $this->assetRepository->getIdentifierCode();
    }
}

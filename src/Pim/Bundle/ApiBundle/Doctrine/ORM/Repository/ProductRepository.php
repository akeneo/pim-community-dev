<?php

namespace Pim\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface as CatalogProductRepositoryInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements ProductRepositoryInterface
{
    /** @var CatalogProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param EntityManager                     $em
     * @param string                            $className
     * @param CatalogProductRepositoryInterface $productRepository
     */
    public function __construct(EntityManager $em, $className, CatalogProductRepositoryInterface $productRepository)
    {
        parent::__construct($em, $em->getClassMetadata($className));

        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterOffset(ProductQueryBuilderInterface $pqb, $limit, $offset)
    {
        $qb = clone $pqb->getQueryBuilder();

        $rootAlias = $qb->getRootAliases()[0];

        return $qb
            ->orderBy(sprintf('%s.id', $rootAlias), 'ASC')
            ->groupBy(sprintf('%s.id', $rootAlias))
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterIdentifier(ProductQueryBuilderInterface $pqb, $limit, $searchAfterIdentifier)
    {
        $qb = clone $pqb->getQueryBuilder();

        $rootAlias = $qb->getRootAliases()[0];

        if (null !== $searchAfterIdentifier) {
            $qb->andWhere(sprintf('%s.id > :id', $rootAlias))
                ->setParameter(':id', $searchAfterIdentifier);
        }

        return $qb
            ->orderBy(sprintf('%s.id', $rootAlias), 'ASC')
            ->groupBy(sprintf('%s.id', $rootAlias))
            ->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function count(ProductQueryBuilderInterface $pqb)
    {
        try {
            $qb = clone $pqb->getQueryBuilder();

            $rootAlias = $qb->getRootAliases()[0];

            return (int) $qb
                ->select(sprintf('COUNT(DISTINCT %s.id)', $rootAlias))
                ->setMaxResults(null)
                ->setFirstResult(null)
                ->resetDQLParts(['orderBy', 'groupBy'])
                ->getQuery()
                ->getSingleScalarResult();
        } catch (UnexpectedResultException $e) {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return $this->productRepository->getIdentifierProperties();
    }
}

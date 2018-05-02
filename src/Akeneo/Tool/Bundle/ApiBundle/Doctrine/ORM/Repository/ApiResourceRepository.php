<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;

/**
 * Repository for basic entities in the API
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiResourceRepository extends EntityRepository implements ApiResourceRepositoryInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $identifiableRepository;

    /**
     * @param EntityManager                         $em
     * @param string                                $className
     * @param IdentifiableObjectRepositoryInterface $identifiableRepository
     */
    public function __construct(
        EntityManager $em,
        $className,
        IdentifiableObjectRepositoryInterface $identifiableRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));

        $this->identifiableRepository = $identifiableRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->identifiableRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function searchAfterOffset(array $criteria, array $orders, $limit, $offset)
    {
        $qb = $this->createQueryBuilder('r');

        foreach ($criteria as $field => $criterion) {
            $qb->andWhere($qb->expr()->eq(sprintf('r.%s ', $field), $qb->expr()->literal($criterion)));
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
    public function count(array $criteria = [])
    {
        try {
            $qb = $this->createQueryBuilder('r');

            foreach ($criteria as $field => $criterion) {
                $qb->andWhere($qb->expr()->eq(sprintf('r.%s ', $field), $qb->expr()->literal($criterion)));
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
    public function getIdentifierProperties()
    {
        return $this->identifiableRepository->getIdentifierProperties();
    }
}

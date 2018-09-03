<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface as CatalogAttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;

/**
 * Attribute repository for the API
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends EntityRepository implements AttributeRepositoryInterface
{
    /** @var CatalogAttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param EntityManager                       $em
     * @param string                              $className
     * @param CatalogAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        EntityManager $em,
        $className,
        CatalogAttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($em, $em->getClassMetadata($className));

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->attributeRepository->findOneByIdentifier($identifier);
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
    public function getIdentifierProperties()
    {
        return $this->attributeRepository->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        return $this->attributeRepository->getIdentifierCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaAttributeCodes()
    {
        return $this->attributeRepository->findMediaAttributeCodes();
    }
}

<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductRepository extends ProductRepository implements PublishedProductRepositoryInterface
{
    /**
     * Expected by interface but we let ORM entity repository work with its magic here
     *
     * {@inheritdoc}
     */
    public function findOneByOriginalProductId($originalId)
    {
        return parent::findOneByOriginalProductId($originalId);
    }

    /**
     * {@inheritdoc}
     */
    public function findByOriginalProductIds(array $originalIds)
    {
        return parent::findBy(['originalProductId' => $originalIds]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsMapping()
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp.id, pp.originalProductId');

        $ids = [];
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $ids[intval($row['originalProductId'])] = intval($row['id']);
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForFamily(Family $family)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->andWhere('pp.family = :family')
            ->setParameter('family', $family)
            ->select('COUNT(pp.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForCategory(CategoryInterface $category)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->andWhere(':category MEMBER OF pp.categories')
            ->setParameter('category', $category)
            ->select('COUNT(pp.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttribute(AbstractAttribute $attribute)
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForGroup(Group $group)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->andWhere(':group MEMBER OF pp.groups')
            ->setParameter('group', $group)
            ->select('COUNT(pp.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAssociationType(AssociationType $associationType)
    {
        throw new \Exception('Not yet implemented');
    }
}

<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
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
            ->setParameter('family', $family);

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForCategoryAndChildren(CategoryInterface $category)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->innerJoin('pp.categories', 'c')
            ->andWhere($qb->expr()->between('c.left', $category->getLeft(), $category->getRight()))
            ->andWhere($qb->expr()->between('c.right', $category->getLeft(), $category->getRight()))
            ->andWhere($qb->expr()->eq('c.root', $category->getRoot()));

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttribute(AbstractAttribute $attribute)
    {
        $qb = $this->findAllByAttributesQB([$attribute]);

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForGroup(Group $group)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->andWhere(':group MEMBER OF pp.groups')
            ->setParameter('group', $group);

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAssociationType(AssociationType $associationType)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->innerJoin('pp.associations', 'ppa')
            ->andWhere('ppa.associationType = :association_type')
            ->setParameter('association_type', $associationType);

        return $this->getCountFromQB($qb);
    }

    /**
     * Return the result count from a query builder object
     *
     * @param QueryBuilder $qb
     *
     * @return mixed
     */
    protected function getCountFromQB(QueryBuilder $qb)
    {
        $rootAlias = current($qb->getRootAliases());
        $qb->select(sprintf("COUNT(%s.id)", $rootAlias));

        return $qb->getQuery()->getSingleScalarResult();
    }
}

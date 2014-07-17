<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
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
     * {@inheritdoc}
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct)
    {
        return $this->findOneBy(['originalProduct' => $originalProduct->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByOriginalProducts(array $originalProducts)
    {
        $originalIds = [];
        foreach ($originalProducts as $product) {
            $originalIds[] = $product->getId();
        }

        $qb = $this->createQueryBuilder('pp');
        $qb
            ->where($qb->expr()->in('pp.originalProduct', ':originalIds'))
            ->setParameter(':originalIds', $originalIds)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedVersionIdByOriginalProductId($originalId)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->select('IDENTITY(pp.version) AS version_id')
            ->where('pp.originalProduct = :originalId')
            ->setParameter('originalId', $originalId)
        ;

        try {
            $versionId = (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            $versionId = null;
        }

        return $versionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsMapping(array $originalIds = [])
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp.id AS published_id, IDENTITY(pp.originalProduct) AS original_id');
        if (!empty($originalIds)) {
            $qb->andWhere($qb->expr()->in('pp.originalProduct', $originalIds));
        }

        $ids = [];
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $ids[intval($row['original_id'])] = intval($row['published_id']);
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
    public function countPublishedProductsForCategoryAndChildren($categoryIds)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->innerJoin('pp.categories', 'c')
            ->andWhere($qb->expr()->in('c.id', $categoryIds));

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
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttributeOption(AttributeOption $option)
    {
        $qb = $this->createQueryBuilder('pp');

        if ($option->getAttribute()->getAttributeType() === 'pim_catalog_simpleselect') {
            $qb
                ->innerJoin('pp.values', 'ppv', 'WITH', $qb->expr()->eq('ppv.option', $option->getId()));
        } else {
            $qb
                ->innerJoin('pp.values', 'ppv')
                ->innerJoin('ppv.options', 'ppo', 'WITH', $qb->expr()->eq('ppo.id', $option->getId()));
        }

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

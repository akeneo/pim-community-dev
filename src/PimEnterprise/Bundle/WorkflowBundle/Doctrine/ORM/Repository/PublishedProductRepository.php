<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
    public function findOneByOriginalProductId($originalProductId)
    {
        return $this->findOneBy(['originalProduct' => $originalProductId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByVersionId($versionId)
    {
        return $this->findOneBy(['version' => $versionId]);
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
            ->setParameter(':originalIds', $originalIds);

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
            ->setParameter('originalId', $originalId);

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
    public function countPublishedProductsForFamily(FamilyInterface $family)
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
    public function countPublishedProductsForCategory(CategoryInterface $category)
    {
        $categoryIds = $this->getObjectManager()
            ->getRepository(ClassUtils::getClass($category))->getAllChildrenIds($category);
        $categoryIds[] = $category->getId();

        $qb = $this->createQueryBuilder('pp');
        $qb
            ->innerJoin('pp.categories', 'c')
            ->andWhere($qb->expr()->in('c.id', $categoryIds));

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttribute(AttributeInterface $attribute)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->innerJoin('pp.values', 'ppv', 'WITH', $qb->expr()->eq('ppv.attribute', $attribute->getId()));

        return $this->getCountFromQB($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForGroup(GroupInterface $group)
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
    public function countPublishedProductsForAssociationType(AssociationTypeInterface $associationType)
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
    public function countPublishedProductsForAttributeOption(AttributeOptionInterface $option)
    {
        $qb = $this->createQueryBuilder('pp');

        if ($option->getAttribute()->getAttributeType() === AttributeTypes::OPTION_SIMPLE_SELECT) {
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

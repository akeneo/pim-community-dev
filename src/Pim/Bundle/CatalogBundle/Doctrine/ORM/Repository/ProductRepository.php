<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements
    ProductRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    CursorableRepositoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $queryBuilderFactory;

    /** @var ConfigurationRegistryInterface */
    protected $referenceDataRegistry;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /**
     * {@inheritdoc}
     */
    public function setProductQueryBuilderFactory(ProductQueryBuilderFactoryInterface $factory)
    {
        $this->queryBuilderFactory = $factory;
    }

    /**
     * Set group repository
     *
     * @param GroupRepositoryInterface $groupRepository
     *
     * @return ProductRepository
     */
    public function setGroupRepository(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;

        return $this;
    }

    /**
     * Set reference data registry
     *
     * @param ConfigurationRegistryInterface $registry
     *
     * @return ProductRepositoryInterface
     */
    public function setReferenceDataRegistry(ConfigurationRegistryInterface $registry = null)
    {
        $this->referenceDataRegistry = $registry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.identifier IN (:identifiers)')
            ->setParameter('identifiers', $identifiers);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(GroupInterface $variantGroup, array $criteria = [])
    {
        $qb = $this->findAllForVariantGroupQB($variantGroup, $criteria);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['identifier'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEligibleProductsForVariantGroup($variantGroupId)
    {
        // TODO
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('a.id')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->where($qb->expr()->in('p.id', $productIds))
            ->distinct(true);

        $attributes = $qb->getQuery()->getArrayResult();
        $attributeIds = [];
        foreach ($attributes as $attribute) {
            $attributeIds[] = $attribute['id'];
        }

        return $attributeIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsByGroup(GroupInterface $group, $maxResults)
    {
        $products = $this
            ->createQueryBuilder('p')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->setMaxResults($maxResults)
            ->execute();

        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByGroup(GroupInterface $group)
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countAll()
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily($productId, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.family', 'f')
            ->leftJoin('f.attributes', 'a')
            ->where('p.id = :id')
            ->andWhere('a.code = :code')
            ->setParameters([
                'id'   => $productId,
                'code' => $attributeCode,
            ])
            ->setMaxResults(1);

        return count($queryBuilder->getQuery()->getArrayResult()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function findProductIdsForVariantGroup(GroupInterface $variantGroup, array $criteria = [])
    {
        $queryBuilder = $this->findAllForVariantGroupQB($variantGroup, $criteria);

        return $queryBuilder->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithOffsetAndSize($offset = 0, $size = 100)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->setFirstResult($offset)
            ->setMaxResults($size);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedProductIds(ProductInterface $product)
    {
        $qb = $this->createQueryBuilder('p')
            ->select(
                'a.id AS association_id',
                't.code AS association_type_code',
                'pa.id AS product_id',
                'pa.identifier AS product_identifier'
            )->innerJoin('p.associations', 'a')
            ->innerJoin('a.associationType', 't')
            ->innerJoin('a.products', 'pa')
            ->where('p.id = :productId')
            ->setParameter(':productId', $product->getId());

        return $qb->getQuery()->execute();
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $criteria
     *
     * @return ProductQueryBuilderInterface
     */
    protected function findAllForVariantGroupQB(GroupInterface $variantGroup, array $criteria = [])
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        foreach ($criteria as $item) {
            $value = null;

            if (isset($item['option'])) {
                $value = $item['option'];
                $queryBuilder->addFilter($item['attribute']->getCode(), Operators::IN_LIST, [$value->getCode()]);
            }

            if (isset($item['referenceData'])) {
                $value = $item['referenceData']['data'];
                $queryBuilder->addFilter($item['attribute']->getCode(), Operators::IN_LIST, [$value->getCode()]);
            }
        }

        $queryBuilder->addFilter('variant_group', Operators::IN_LIST, [$variantGroup->getCode()]);

        return $queryBuilder;
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Hybrid Product repository. This class implements ProductRepositoryInterface
 * and have access to EntityManager and ODM ProductRepository, as some
 * functions need to access both worlds
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HybridProductRepository implements ProductRepositoryInterface,
 ReferableEntityRepositoryInterface
{
    /**
     * Product repository from ODM
     *
     * @var DocumentRepository
     */
    protected $odmRepository;

    /**
     * ORM EntityManager to access ORM entities
     * 
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Category class
     *
     * @var string
     */
    protected $categoryClass;

    /**
     * @param DocumentRepository $odmRepository
     * @param EntityManager      $entityManager
     * @param CategoryClass      $categoryClass
     */
    public function __construct(
        DocumentRepository $odmRepository,
        EntityManager $entityManager,
        $categoryClass
    ) {
        $this->odmRepository = $odmRepository;
        $this->entityManager = $entityManager;
        $this->categoryClass = $categoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByAttributes(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this->odmRepository->findAllByAttributes($attributes, $criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function buildByScope($scope)
    {
        return $this->odmRepository->buildByScope($scope);
    }

    /**
     * {@inheritdoc}
     */
    public function buildByChannelAndCompleteness(Channel $channel)
    {
        return $this->odmRepository->buildByChannelAndCompleteness($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExistingFamily()
    {
        return $this->odmRepository->findByExistingFamily();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        return $this->odmRepository->findByIds($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array())
    {
        return $this->odmRepository->findByAllForVariantGroup($variantGroup, $criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        return $this->odmRepository->getFullProduct($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        $categories = $product->getCategories();
        $categoriesIds = array();
        foreach ($categories as $category) {
            $categoriesIds[] = $category->getId();
        }

        $categoryRepository = $this->entityManager->getRepository($this->categoryClass);

        $categoryTable = $this->entityManager->getClassMetadata($this->categoryClass)->getTableName();

        $categoriesIds = implode(',',$categoriesIds);
        $sql = "SELECT".
               "    tree.id AS tree_id,".
               "    COUNT(category.id) AS product_count".
               "  FROM $categoryTable tree".
               "  LEFT JOIN $categoryTable category".
               "    ON category.root = tree.id".
               " AND category.id IN ($categoriesIds)".
               " WHERE tree.parent_id IS NULL".
               " GROUP BY tree.id";

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();

        $productCounts = $stmt->fetchAll();
        $trees = array();
        foreach ($productCounts as $productCount) {
            $tree = array();
            $tree['productCount'] = $productCount['product_count'];
            $tree['tree'] = $categoryRepository->find($productCount['tree_id']);
            $trees[] = $tree;
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig()
    {
        return $this->odmRepository->getFlexibleConfig();
    }

    /**
     * Set flexible entity config
     *
     * @param array $config
     *
     * @return FlexibleEntityRepository
     */
    public function setFlexibleConfig($config)
    {
        return $this->odmRepository->setFlexibleConfig($config);
    }

    /**
     * Return asked locale code or default one
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->odmRepository->getLocale();
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setLocale($code)
    {
        return $this->odmRepository->setLocale($code);
    }

    /**
     * Return asked scope code or default one
     *
     * @return string
     */
    public function getScope()
    {
        return $this->odmRepository->getScope();
    }

    /**
     * Set scope code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setScope($code)
    {
        return $this->odmRepository->setScope($code);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByWithValues($id)
    {
        return $this->odmRepository->findOneByWithValues($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->odmRepository->findByReference($code);
    }


    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return $this->odmRepository->getReferenceProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        return $this->odmRepository->valueExists($value);
    }

    /**
     * {@inheritdoc}
     */
    public function countProductsPerChannels()
    {
        return $this->odmRepository->countProductsPerChannels();
    }

    /**
     * {@inheritdoc}
     */
    public function countCompleteProductsPerChannels()
    {
        return $this->odmRepository->countCompleteProductsPerChannels();
    }

    /**
     * {@inheritdoc}
     */
    public function setFlexibleQueryBuilder($flexibleQB)
    {
        return $this->odmRepository->setFlexibleQueryBuilder($flexibleQB);

    }

    /**
     * {@inheritdoc}
     */
    protected function getFlexibleQueryBuilder($qb)
    {
        return $this->odmRepository->getFlexibleQueryBuilder($qb);
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        return $this->odmRepository->createDatagridQueryBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByAttribute($qb, Attribute $attribute, $value, $operator = '=')
    {
        return $this->odmRepository->applyFilterByAttribute($qb, $attribute, $value, $operator);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByField($qb, $field, $value, $operator = '=')
    {
        return $this->odmRepository->applyFilterByField($qb, $field, $value, $operator);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByAttribute($qb, Attribute $attribute, $direction)
    {
        return $this->odmRepository->applySorterByAttribute($qb, $attribute, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByField($qb, $field, $direction)
    {
        return $this->odmRepository->applySorterByField($qb, $field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, $productIds, $include)
    {
        return $this->odmRepository->applyFilterByIds($qb, $productIds, $include);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByGroupIds($qb, $groupIds)
    {
        return $this->odmRepository->applyFilterByGroupIds($qb, $groupIds);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByFamilyIds($qb, $familyIds)
    {
        return $this->odmRepository->applyFilterByFamilyIds($qb, $familyIds);
    }
}

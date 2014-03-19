<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Product repository
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends DocumentRepository implements ProductRepositoryInterface,
 ReferableEntityRepositoryInterface
{
    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * @param ProductQueryBuilder
     */
    protected $productQB;

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
     * Set the EntityManager
     *
     * @param EntityManager $entityManager
     *
     * @return ProductRepository $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set the Category class
     *
     * @param string $categoryClass
     *
     * @return ProductRepository $this
     */
    public function setCategoryClass($categoryClass)
    {
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
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildByScope($scope)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildByChannelAndCompleteness(Channel $channel)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findByExistingFamily()
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array())
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        $categories = $product->getCategories();
        $categoryIds = array();
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        $categoryRepository = $this->entityManager->getRepository($this->categoryClass);

        $categoryTable = $this->entityManager->getClassMetadata($this->categoryClass)->getTableName();

        $categoryIds = implode(',', $categoryIds);

        if (!empty($categoryIds)) {
            $sql = "SELECT".
                   "    tree.id AS tree_id,".
                   "    COUNT(category.id) AS product_count".
                   "  FROM $categoryTable tree".
                   "  LEFT JOIN $categoryTable category".
                   "    ON category.root = tree.id".
                   " AND category.id IN ($categoryIds)".
                   " WHERE tree.parent_id IS NULL".
                   " GROUP BY tree.id";
        } else {
            $sql = "SELECT".
                   "    tree.id AS tree_id,".
                   "    '0' AS product_count".
                   "  FROM $categoryTable tree".
                   "  LEFT JOIN $categoryTable category".
                   "    ON category.root = tree.id".
                   " WHERE tree.parent_id IS NULL".
                   " GROUP BY tree.id";
        }

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
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        $products = $this->getProductIdsInCategories($categoryIds);

        return array_keys(iterator_to_array($products));
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        return $this->getProductsCountInCategories($categoryIds);
    }

    /**
     * Return categories ids provided by the categoryQb or by the provided category
     *
     * @param CategoryInterface $category
     * @param OrmQueryBuilder   $categoryQb
     *
     * @return array $categoryIds
     */
    protected function getCategoryIds(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = array();

        if (null !== $categoryQb) {
            $categoryAlias = $categoryQb->getRootAlias();
            $categories = $categoryQb->select('PARTIAL '.$categoryAlias.'.{id}')->getQuery()->getArrayResult();
        } else {
            $categories = array(array('id' => $category->getId()));
        }

        foreach ($categories as $category) {
            $categoryIds[] = $category['id'];
        }

        return $categoryIds;
    }

    /**
     * Return a cursor on the product ids belonging the categories
     * with category ids provided
     *
     * @param array $categoryIds
     *
     * @return Cursor mongoDB cursor on the Ids
     */
    public function getProductIdsInCategories(array $categoryIds)
    {
        if (count($categoryIds) === 0) {
            return 0;
        }

        $qb = $this->createQueryBuilder()
            ->hydrate(false)
            ->field('categories')->in($categoryIds)
            ->select('_id');

        return $qb->getQuery()->execute();
    }

    /**
     * Return the number of products matching the categories ids provided
     *
     * @param array $categoriesIds
     *
     * @return int $productsCount
     */
    public function getProductsCountInCategories(array $categoriesIds)
    {
        return $this->getProductIdsInCategories($categoriesIds)->count();
    }

    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
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
        $this->flexibleConfig = $config;

        return $this;
    }

    /**
     * Return asked locale code or default one
     *
     * @return string
     */
    public function getLocale()
    {
        if (!$this->locale) {
            $this->locale = $this->flexibleConfig['default_locale'];
        }

        return $this->locale;
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
        $this->locale = $code;

        return $this;
    }

    /**
     * Return asked scope code or default one
     *
     * @return string
     */
    public function getScope()
    {
        if (!$this->scope) {
            $this->scope = $this->flexibleConfig['default_scope'];
        }

        return $this->scope;
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
        $this->scope = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByWithValues($id)
    {
        // FIXME_MONGO Shortcut, but must do the same thing
        // than the ORM one
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return $this->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $qb = $this->createQueryBuilder();
        $this->applyFilterByAttribute($qb, $value->getAttribute(), $value->getData());
        $result = $qb->hydrate(false)->getQuery()->getSingleResult();

        $foundValueId = null;
        if ((1 === count($result)) && isset($result['_id'])) {
            $foundValueId = $result['_id']->id;
        }

        return (
            (0 !== count($result)) &&
            ($value->getId() === $foundValueId)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function countProductsPerChannels()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function countCompleteProductsPerChannels()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function setProductQueryBuilder($productQB)
    {
        $this->productQB = $productQB;

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    protected function getProductQueryBuilder($qb)
    {
        if (!$this->productQB) {
            throw new \LogicException('Flexible query builder must be configured');
        }

        $this->productQB
            ->setQueryBuilder($qb)
            ->setLocale($this->getLocale())
            ->setScope($this->getScope());

        return $this->productQB;
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder();

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByAttribute($qb, AbstractAttribute $attribute, $value, $operator = '=')
    {
        $this->getProductQueryBuilder($qb)->addAttributeFilter($attribute, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByField($qb, $field, $value, $operator = '=')
    {
        $this->getProductQueryBuilder($qb)->addFieldFilter($field, $operator, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByAttribute($qb, AbstractAttribute $attribute, $direction)
    {
        $this->getProductQueryBuilder($qb)->addAttributeSorter($attribute, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByField($qb, $field, $direction)
    {
        $this->getProductQueryBuilder($qb)->addFieldSorter($field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByIds($qb, array $productIds, $include)
    {
        if ($include) {
            $qb->addAnd($qb->expr()->field('id')->in($productIds));
        } else {
            $qb->addAnd($qb->expr()->field('id')->notIn($productIds));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByGroupIds($qb, array $groupIds)
    {
        $qb->addAnd($qb->expr()->field('groups')->in($groupIds));
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByFamilyIds($qb, array $familyIds)
    {
        $qb->addAnd($qb->expr()->field('family')->in($familyIds));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $ids)
    {
        if (empty($ids)) {
            throw new \LogicException('No products to remove');
        }

        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }
}

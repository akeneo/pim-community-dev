<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface as CatalogCategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Pim\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepository implements
    ProductCategoryRepositoryInterface,
    ItemCategoryRepositoryInterface,
    CategoryFilterableRepositoryInterface
{
    /**
     * ORM EntityManager to access ORM entities
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * MongoDBODM Document Manager to access ODM entities
     *
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * Category class
     *
     * @var string
     */
    protected $categoryClass;

    /**
     * @param DocumentManager $docManager
     * @param string          $documentName
     * @param EntityManager   $entManager
     * @param string          $categoryClass
     */
    public function __construct(DocumentManager $docManager, $documentName, EntityManager $entManager, $categoryClass)
    {
        $this->documentManager = $docManager;
        $this->entityManager   = $entManager;
        $this->documentName    = $documentName;
        $this->categoryClass   = $categoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCountByTree(ProductInterface $product)
    {
        return $this->getItemCountByTree($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(CatalogCategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        return $this->getItemIdsInCategory($category, $categoryQb);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(CatalogCategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        return $this->getItemsCountInCategory($category, $categoryQb);
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
        $categoryIds = [];

        if (null !== $categoryQb) {
            $categoryAlias = $categoryQb->getRootAlias();
            $categories = $categoryQb->select('PARTIAL '.$categoryAlias.'.{id}')->getQuery()->getArrayResult();
        } else {
            $categories = [['id' => $category->getId()]];
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

        $qb = $this->documentManager->createQueryBuilder($this->documentName)
            ->hydrate(false)
            ->field('categoryIds')->in($categoryIds)
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
    public function applyFilterByUnclassified($qb)
    {
        $qb->addAnd($qb->expr()->field('categoryIds')->size(0));
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIds($qb, array $categoryIds, $include = true)
    {
        if ($include) {
            $qb->addAnd($qb->expr()->field('categoryIds')->in($categoryIds));
        } else {
            $qb->addAnd($qb->expr()->field('categoryIds')->notIn($categoryIds));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterByCategoryIdsOrUnclassified($qb, array $categoryIds)
    {
        $qb->addAnd(
            $qb->expr()
                ->addOr($qb->expr()->field('categoryIds')->in($categoryIds))
                ->addOr($qb->expr()->field('categoryIds')->size(0))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItemCountByTree($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected a "Pim\Bundle\CatalogBundle\Model\ProductInterface", got a "%s"',
                    ClassUtils::getClass($product)
                )
            );
        }

        $categories = $product->getCategories();
        $categoryIds = [];
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
        $trees = [];
        foreach ($productCounts as $productCount) {
            $tree = [];
            $tree['productCount'] = $productCount['product_count'];
            $tree['tree'] = $categoryRepository->find($productCount['tree_id']);
            $trees[] = $tree;
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemIdsInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        $products = $this->getProductIdsInCategories($categoryIds);

        return array_keys(iterator_to_array($products));
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(CategoryInterface $category, OrmQueryBuilder $categoryQb = null)
    {
        $categoryIds = $this->getCategoryIds($category, $categoryQb);

        return $this->getProductsCountInCategories($categoryIds);
    }
}

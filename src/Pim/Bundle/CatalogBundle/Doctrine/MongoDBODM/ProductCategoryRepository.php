<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

/**
 * Product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * ORM EntityManager to access ORM entities
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * MongoDBODM Document Manager to access ORM entities
     *
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * @param DocumentManager  $dm
     * @param string           $documentName
     * @param FamilyRepository $familyRepository
     */
    public function __construct(DocumentManager $documentManager, $documentName, EntityManager $entityManager)
    {
        $this->documentManager = $documentManager;
        $this->entityManager   = $entityManager;
        $this->documentName    = $documentName;
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
}

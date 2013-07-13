<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository;

/**
 * Category repository
 * Override SegmentRepository of OroSegmentationTreeBundle
 *     Allow to count products linked to nodes
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryRepository extends SegmentRepository
{
    /**
     * Get query builder for all existitng category trees
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTreesQB()
    {
        return $this->getChildrenQueryBuilder(null, true, null, 'ASC', null);
    }

    /**
     * Shortcut to get all children query builder
     *
     * @param Category $category    the requested node
     * @param boolean  $includeNode true to include actual node in query result
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllChildrenQueryBuilder(Category $category, $includeNode = false)
    {
        return $this->getChildrenQueryBuilder($category, false, null, 'ASC', $includeNode);
    }

    /**
     * Count products linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param Category $category    the requested node
     * @param boolean  $onlyActual  true to take only actual node
     * @param boolean  $includeNode true to include actual node in query result
     *
     * @return integer
     */
    public function countProductsLinked(Category $category, $onlyActual = true, $includeNode = true)
    {
        $qb = ($onlyActual) ?
            $this->getNodeQueryBuilder($category) :
            $this->getAllChildrenQueryBuilder($category, $includeNode);

        $rootAlias = $qb->getRootAliases();
        $firstRootAlias = $rootAlias[0];

        $qb->select($qb->expr()->count('p'))
           ->join($firstRootAlias .'.products', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create a query builder with just a link to the category passed in parameter
     *
     * @param Category $category
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getNodeQueryBuilder(Category $category)
    {
        $qb = $this->createQueryBuilder('ps');
        $qb->where('ps.id = :nodeId')
           ->setParameter('nodeId', $category->getId());

        return $qb;
    }

    /**
     * Return the number of times the product is present in each tree
     *
     * @param ProductInterface $product The product to look for in the trees
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'productsCount'=>integer
     */
    public function getProductsCountByTree(ProductInterface $product)
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $nodeTable = $config['useObjectClass'];

        $dql = "SELECT tree, COUNT(product.id)".
               "  FROM $nodeTable tree".
               "  JOIN $nodeTable category".
               "  WITH category.root = tree.id".
               "  LEFT JOIN category.products product".
               " WHERE (product.id = :productId OR product.id IS NULL)".
               " GROUP BY tree.id";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('productId', $product->getId());

        $rawTrees = $query->getResult();
        $trees = array();
        $treeKeys = array('tree','productsCount');

        foreach ($rawTrees as $rawTree) {
            $trees[] = array_combine($treeKeys, $rawTree);
        }

        return $trees;

    }

    /**
     * Get a collection of categories based on the array of id provided
     *
     * @param array $categoriesIds
     *
     * @return Collection of categories
     */
    public function getCategoriesByIds(array $categoriesIds = array())
    {
        if (count($categoriesIds) === 0) {
            return new ArrayCollection();
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN(:categoriesIds)');

        $qb->setParameter('categoriesIds', $categoriesIds);

        $result = $qb->getQuery()->getResult();
        $result = new ArrayCollection($result);

        return $result;
    }

    /**
     * Get a tree filled with children and their parents
     *
     * @param array $parentsIds parent ids
     *
     * @return array
     */
    public function getTreeFromParents(array $parentsIds)
    {
        if (count($parentsIds) === 0) {
            return array();
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN (:parentsIds) OR node.parent IN (:parentsIds)');

        $qb->setParameter('parentsIds', $parentsIds);

        $nodes = $qb->getQuery()->getResult();

        return $this->buildTreeNode($nodes);
    }

    /**
     * Return associative array of category id to title
     *
     * @return array
     */
    public function getAllIdToTitle()
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $choices = array();

        $qb = $this->_em->createQueryBuilder();
        $qb->select('category, translations')
            ->from($config['useObjectClass'], 'category', 'category.id')
            ->leftJoin('category.translations', 'translations');

        $categories = $qb->getQuery()->execute(array(), \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        $choices = array();

        foreach ($categories as $category) {
            $choices[$category->getId()] = $category->getTitle();
        }

        return $choices;
    }
}

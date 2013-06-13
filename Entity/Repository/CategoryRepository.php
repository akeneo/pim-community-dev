<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Entity\Product;
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
     * Create a query builder to get a tree filled with nodes only down to the provided nodes
     * 
     * @param Category   $parent      The parent node
     * @param Collection $categories  The categories that should be included in the tree with their ancestors and
     *                                their siblings
     * @param boolean    $includeNode If true, will include the parent node in the response
     */
    protected function getMatchingHierarchyQueryBuilder(Category $parent = null, Collection $categories, $includeNode = false)
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node');

        $qb = $this->getNodesHierarchyQueryBuilder($parent, false, array(), $includeNode);

        $categoriesCondition = $qb->expr()->orx();

        foreach ($categories as $category) {
            $categoryLeft = $category->getLeft();
            $categoryRight = $category->getRight();
            $categoryParent = $category->getParent();

            if ($categoryParent != null) {
                $categoryCondition = $qb->expr()->orx(
                    $qb->expr()->andx(
                        $qb->expr()->lt('node.' . $config['left'], $categoryLeft),
                        $qb->expr()->gt('node.' . $config['right'], $categoryRight)
                    ),
                    $qb->expr()->eq('node.' . $config['parent'], $categoryParent->getId())
                );
            } else {
                $categoryCondition = $qb->expr()->andx(
                    $qb->expr()->lt('node.' . $config['left'], $categoryLeft),
                    $qb->expr()->gt('node.' . $config['right'], $categoryRight)
                );
            }
                
            $categoriesCondition->add( $categoryCondition );
        }
        $qb->andWhere($categoriesCondition);
        return $qb;
    }

    public function getMatchingHierarchy(Category $parent = null, Collection $categories, $includeNode = false)
    {
        $qb = $this->getMatchingHierarchyQueryBuilder($parent, $categories, $includeNode);
        $nodes = $qb->getQuery()->getResult();

        return $this->buildTreeNode($nodes);
    }
   
    /**
     * Return the number of times the product is present in each tree
     *
     * @param Product $product The product to look for in the trees
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'productsCount'=>integer
     */
    public function getProductsCountByTree(Product $product)
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

        foreach($rawTrees as $rawTree) {
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

        $qb->setParameter('categoriesIds',$categoriesIds);

        $result = $qb->getQuery()->getResult();
        $result = new ArrayCollection($result);

        return $result;
    }
}

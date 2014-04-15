<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Category repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRepository extends NestedTreeRepository implements ReferableEntityRepositoryInterface
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
     * Count children for a given category.
     *
     * @param Category $category   the requested node
     * @param boolean  $onlyDirect true to cound only direct children
     *
     * @return integer
     */
    public function countChildren(CategoryInterface $category, $onlyDirect = false)
    {
        $qb = ($onlyDirect) ?
            $this->getNodeQueryBuilder($category) :
            $this->getAllChildrenQueryBuilder($category, false);

        $rootAlias = $qb->getRootAliases();
        $firstRootAlias = $rootAlias[0];

        $qb->select($qb->expr()->count($firstRootAlias));

        return $qb->getQuery()->getSingleScalarResult();
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
     * Create a query builder with just a link to the category passed in parameter
     *
     * @param Category $category
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getNodeQueryBuilder(CategoryInterface $category)
    {
        $qb = $this->createQueryBuilder('ps');
        $qb->where('ps.id = :nodeId')
           ->setParameter('nodeId', $category->getId());

        return $qb;
    }

    /**
     * Shortcut to get all children query builder
     *
     * @param Category $category    the requested node
     * @param boolean  $includeNode true to include actual node in query result
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllChildrenQueryBuilder(CategoryInterface $category, $includeNode = false)
    {
        return $this->getChildrenQueryBuilder($category, false, null, 'ASC', $includeNode);
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array('code');
    }
    /**
     * Return categories ids provided by the categoryQb or by the provided category
     *
     * @param CategoryInterface $category
     * @param OrmQueryBuilder   $categoryQb
     *
     * @return array $categoryIds
     */
    public function getCategoryIds(CategoryInterface $category, QueryBuilder $categoryQb = null)
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
     * Get children from a parent id
     *
     * @param integer $parentId
     *
     * @return ArrayCollection
     */
    public function getChildrenByParentId($parentId)
    {
        $parent = $this->findOneBy(array('id' => $parentId));

        return $this->getChildren($parent, true);
    }

    /**
     * Get children tree from a parent id.
     * If the select node id is provided, the tree will be returned
     * down to the node specified by select node id. Otherwise, the
     * whole tree will be returned
     *
     * @param integer $parentId
     * @param integer $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getChildrenTreeByParentId($parentId, $selectNodeId = false)
    {
        $children = array();

        $parent = $this->findOneBy(array('id' => $parentId));

        if ($selectNodeId === false) {
            $children = $this->childrenHierarchy($parent);
        } else {
            $selectNode = $this->findOneBy(array('id' => $selectNodeId));
            if ($selectNode != null) {

                $meta = $this->getClassMetadata();
                $config = $this->listener->getConfiguration($this->_em, $meta->name);

                $selectPath = $this->getPath($selectNode);
                $qb = $this->getNodesHierarchyQueryBuilder($parent);

                // Remove the node itself from his ancestor
                array_pop($selectPath);

                $ancestorsIds = array();

                foreach ($selectPath as $ancestor) {
                    $ancestorsIds[] = $ancestor->getId();
                }

                $qb->andWhere(
                    $qb->expr()->in('node.' . $config['parent'], $ancestorsIds)
                );
                $nodes = $qb->getQuery()->getResult();
                $children = $this->buildTreeNode($nodes);
            }
        }
        return $children;
    }

    /**
     * Based on the Gedmo\Tree\RepositoryUtils\buildTreeArray, but with
     * keeping the node as object and able to manage nodes in different branches
     * (the original implementation works with only depth and associate all
     * nodes of depth D+1 to the last node of depth D.)
     *
     * @param array $nodes Must be sorted by increasing depth
     *
     * @return array
     */
    public function buildTreeNode(array $nodes)
    {
        $vectorMap = array();
        $tree = array();
        $childrenIndex = $this->repoUtils->getChildrenIndex();

        foreach ($nodes as $node) {
            if (!isset($vectorMap[$node->getId()])) {
                // Node does not exist, and none of his children has
                // already been in the loop, so we create it.
                $vectorMap[$node->getId()] = array(
                    'item' => $node,
                    $childrenIndex => array()
                );
            } else {
                // Node already existing in the map because a child has been
                // added to his children array. We still need to add the node
                // itself, as only its children property has been created.
                $vectorMap[$node->getId()]['item'] = $node;
            }

            if ($node->getParent() != null) {
                if (!isset($vectorMap[$node->getParent()->getId()])) {
                    // The parent does not exist in the map, create its
                    // children property
                    $vectorMap[$node->getParent()->getId()] = array(
                        $childrenIndex => array()
                    );
                }

                $vectorMap[$node->getParent()->getId()][$childrenIndex][] =& $vectorMap[$node->getId()];
            } else {
                $tree[$node->getId()] =& $vectorMap[$node->getId()];
            }
        }

        if (empty($tree)) {
            // No node found with getParent() == null, meaning the absolute tree
            // root was not part of the set. We try to find the lowest level nodes
            // or a node without item part, meaning that it's a referenced parent but without
            // the node present itself in the set
            $i = 0;
            $foundItemLess = false;
            $nodeIds= array_keys($vectorMap);
            $nodesByLevel = array();

            while ($i < count($nodeIds) && !$foundItemLess) {
                $nodeId = $nodeIds[$i];
                $nodeEntry = $vectorMap[$nodeId];

                if (isset($nodeEntry['item'])) {
                    //$nodesByLevel[$nodeEntry['item']->getLevel()][] = $nodeIds[$i];
                } else {
                    $tree =& $vectorMap[$nodeId][$childrenIndex];
                }
                $i++;
            }
            // $tree still empty there, means we need to pick the lowest level nodes as tree roots
            if (empty($tree)) {
                $lowestLevel = min(array_keys($nodesByLevel));
                foreach ($nodesByLevel[$lowestLevel] as $nodeId) {
                    $tree[$nodeId] =& $vectorMap[$nodeId];
                }
            }
        }

        return $tree;
    }


    /**
     * Search Segment entities from an array of criterias.
     * Search is done on a "%value%" LIKE expression.
     * Criterias are joined with a AND operator
     *
     * @param int   $treeRootId Tree segment root id
     * @param array $criterias  Criterias to apply
     *
     * @return ArrayCollection
     */
    public function search($treeRootId, $criterias)
    {
        $queryBuilder = $this->createQueryBuilder('c');
        foreach ($criterias as $key => $value) {
            $queryBuilder->andWhere('c.'. $key .' LIKE :'. $key)->setParameter($key, '%'. $value .'%');
        }
        $queryBuilder->andWhere('c.root = :rootId')->setParameter('rootId', $treeRootId);

        return $queryBuilder->getQuery()->getResult();
    }
}

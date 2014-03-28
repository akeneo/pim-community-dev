<?php
namespace Pim\Bundle\SegmentationTreeBundle\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Repository for Segment entities
 *
 */
class SegmentRepository extends NestedTreeRepository
{
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

<?php
namespace Oro\Bundle\SegmentationTreeBundle\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Repository for Segment entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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

                $qb = $this->getNodesHierarchyQueryBuilder($parent);

                $qb->andWhere(
                    $qb->expr()->orx(
                        $qb->expr()->lt('node.' . $config['level'], $selectNode->getLevel()),
                        $qb->expr()->eq('node.' . $config['parent'], $selectNode->getParent()->getId())
                    )
                );
                $nodes = $qb->getQuery()->getResult();
                $children = $this->buildTreeNode($nodes);
            }
        }
        return $children;

    }

    /**
     * Based on the Gedmo\Tree\RepositoryUtils\buildTreeArray, but with
     * keeping the node as object
     *
     * @param array $nodes
     *
     * @return array
     */
    public function buildTreeNode(array $nodes)
    {
        $meta = $this->getClassMetadata();
        $nestedTree = array();
        $l = 0;

        if (count($nodes) > 0) {
            // Node Stack. Used to help building the hierarchy
            $stack = array();
            foreach ($nodes as $child) {
                $item = array();
                $item['item'] = $child;
                $item[$this->repoUtils->getChildrenIndex()] = array();
                // Number of stack items
                $l = count($stack);
                // Check if we're dealing with different levels
                while ($l > 0 && $stack[$l - 1]['item']->getLevel() >= $child->getLevel()) {
                    array_pop($stack);
                    $l--;
                }
                // Stack is empty (we are inspecting the root)
                if ($l == 0) {
                    // Assigning the root child
                    $i = count($nestedTree);
                    $nestedTree[$i] = $item;
                    $stack[] = &$nestedTree[$i];
                } else {
                    // Add child to parent
                    $i = count($stack[$l - 1][$this->repoUtils->getChildrenIndex()]);
                    $stack[$l - 1][$this->repoUtils->getChildrenIndex()][$i] = $item;
                    $stack[] = &$stack[$l - 1][$this->repoUtils->getChildrenIndex()][$i];
                }
            }
        }

        return $nestedTree;
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

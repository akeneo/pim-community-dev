<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\ProductBundle\Entity\Category;
use Doctrine\Common\Collections\Collection;

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
     * Create a query builder to get a tree filled with noded only down to the provided nodes
     * 
     * @param Category        $parent The parent node
     * @param ArrayCollection $categories The categories that should be included in the tree with their ancestors and
     *                          their siblings
     */
    protected function getLimitedHierarchyQueryBuilder(Category $parent = null, Collection $categories)
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')

        $root = $this->findOneBy(array('id' => $categories[0]->getRoot()));

        $qb = $this->getNodesHierarchyQueryBuilder($root);

        foreach ($categories as $category) {
            $qb->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->lt('node.' . $config['level'], $category->getLevel()),
                    $qb->expr()->eq('node.' . $config['parent'], $category->getParent()->getId())
                )
            );
            break;
        }
        print_r($qb->getDQL());

        return $qb;
    }

    public function getLimitedHierarchy(Category $parent = null, Collection $categories)
    {
        $qb = $this->getLimitedHierarchyQueryBuilder($parent, $categories);
        $nodes = $qb->getQuery()->getArrayResult();

        print_r($this->buildTreeArray($nodes));

        return $nodes;
    }
}

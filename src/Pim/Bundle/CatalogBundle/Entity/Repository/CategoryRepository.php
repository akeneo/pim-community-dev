<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Category repository
 * Override SegmentRepository of OroSegmentationTreeBundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRepository extends SegmentRepository implements ReferableEntityRepositoryInterface
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
    public function countChildren(Category $category, $onlyDirect = false)
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
    protected function getNodeQueryBuilder(Category $category)
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
    public function getAllChildrenQueryBuilder(Category $category, $includeNode = false)
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
}

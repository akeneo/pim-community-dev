<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\RepositoryInterface as TreeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryRepositoryInterface extends
    TreeRepositoryInterface,
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * Get query builder for all existing category trees
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTreesQB();

    /**
     * Count children for a given category.
     *
     * @param CategoryInterface $category   the requested node
     * @param bool              $onlyDirect true to count only direct children
     *
     * @return integer
     */
    public function countChildren(CategoryInterface $category, $onlyDirect = false);

    /**
     * Get a collection of categories based on the array of id provided
     *
     * @param array $categoriesIds
     *
     * @return Collection of categories
     */
    public function getCategoriesByIds(array $categoriesIds = array());

    /**
     * Get a tree filled with children and their parents
     *
     * @param array $parentsIds parent ids
     *
     * @return array
     */
    public function getTreeFromParents(array $parentsIds);

    /**
     * Shortcut to get all children query builder
     *
     * @param CategoryInterface $category    the requested node
     * @param boolean           $includeNode true to include actual node in query result
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllChildrenQueryBuilder(CategoryInterface $category, $includeNode = false);

    /**
     * Shortcut to get all children ids
     *
     * @param CategoryInterface $parent the parent
     *
     * @return integer[]
     */
    public function getAllChildrenIds(CategoryInterface $parent);

    /**
     * Return categories ids provided by the categoryQb or by the provided category
     *
     * @param CategoryInterface $category
     * @param QueryBuilder      $categoryQb
     *
     * @return array $categoryIds
     */
    public function getCategoryIds(CategoryInterface $category, QueryBuilder $categoryQb = null);

    /**
     * Get children from a parent id
     *
     * @param integer $parentId
     *
     * @return ArrayCollection
     */
    public function getChildrenByParentId($parentId);

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
    public function getChildrenTreeByParentId($parentId, $selectNodeId = false);

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
    public function buildTreeNode(array $nodes);

    /**
     * Search Segment entities from an array of criterias.
     * Search is done on a "%value%" LIKE expression.
     * Criterias are joined with a AND operator
     *
     * @param integer $treeRootId Tree segment root id
     * @param array   $criterias  Criterias to apply
     *
     * @return ArrayCollection
     */
    public function search($treeRootId, $criterias);

    /**
     * Get the Tree path of Nodes by given $node
     *
     * @param object $node
     *
     * @return array - list of Nodes in path
     */
    public function getPath($node);
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Category;

use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;

final class InMemoryCategoryRepository implements
    IdentifiableObjectRepositoryInterface,
    SaverInterface,
    ObjectRepository,
    CategoryRepositoryInterface
{
    /** @var Collection */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->categories->get($code);
    }

    /**
     * {@inheritdoc}
     */
    public function save($category, array $options = [])
    {
        $this->categories->set($category->getCode(), $category);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $categories = [];
        foreach ($this->categories as $category) {
            $keepThisCategory = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($category->$getter() !== $value) {
                    $keepThisCategory = false;
                }
            }

            if ($keepThisCategory) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByIds(array $categoryIds = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByCodes(array $categoryCodes = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeFromParents(array $parentsIds)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildrenIds(CategoryInterface $parent, $includeNode = false)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildrenCodes(CategoryInterface $parent, $includeNode = false)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIdsByCodes(array $codes)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenByParentId($parentId)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenGrantedByParentId(CategoryInterface $parent, array $grantedCategoryIds = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenTreeByParentId($parentId, $selectNodeId = false, array $grantedCategoryIds = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildTreeNode(array $nodes)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($node)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        $trees = [];
        foreach ($this->categories as $category) {
            if (null === $category->getParent()) {
                $trees[] = $category;
            }
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantedTrees(array $grantedCategoryIds = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderedAndSortedByTreeCategories()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getRootNodes($sortByField = null, $direction = 'asc')
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getNodesHierarchy($node = null, $direct = false, array $options = [], $includeNode = false)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function childCount($node = null, $direct = false)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function childrenHierarchy($node = null, $direct = false, array $options = [], $includeNode = false)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildTree(array $nodes, array $options = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function buildTreeArray(array $nodes)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function setChildrenIndex($childrenIndex)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenIndex()
    {
        throw new NotImplementedException(__METHOD__);
    }
}

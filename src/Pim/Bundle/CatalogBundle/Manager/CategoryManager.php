<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $categoryClass;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param string                   $categoryClass
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ObjectManager $om, $categoryClass, EventDispatcherInterface $eventDispatcher)
    {
        $this->om = $om;
        $this->categoryClass = $categoryClass;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Return object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Get a new category instance
     *
     * @return CategoryInterface
     */
    public function getCategoryInstance()
    {
        return new $this->categoryClass;
    }

    /**
     * Return category class name (mainly used in Doctrine context)
     *
     * @return string category class name
     */
    public function getCategoryClass()
    {
        return $this->categoryClass;
    }

    /**
     * Return the entity repository reponsible for the category
     *
     * @return CategoryRepository
     *
     * TODO: Inject CategoryRepository
     */
    public function getEntityRepository()
    {
        return $this->om->getRepository($this->getCategoryClass());
    }

    /**
     * Get a new tree instance
     *
     * @return CategoryInterface
     */
    public function getTreeInstance()
    {
        $tree = $this->getCategoryInstance();
        $tree->setParent(null);

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        return $this->getEntityRepository()->getChildren(null, true, 'created', 'DESC');
    }

    /**
     * Get all direct children for a parent category id.
     * If the $selectNodeId is provided, all the children
     * level needed to provides the selectNode are returned
     *
     * @param integer $parentId
     * @param integer $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId, $selectNodeId = false)
    {
        $children = array();

        $entityRepository = $this->getEntityRepository();

        if ($selectNodeId === false) {
            $children = $entityRepository->getChildrenByParentId($parentId);
        } else {
            $children = $entityRepository->getChildrenTreeByParentId($parentId, $selectNodeId);
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeChoices()
    {
        $trees = $this->getTrees();
        $choices = array();
        foreach ($trees as $tree) {
            $choices[$tree->getId()] = $tree;
        }

        return $choices;
    }

    /**
     * Get a collection of categories based on the array of id provided
     *
     * @param array $categoriesIds
     *
     * @return Collection of categories
     */
    public function getCategoriesByIds($categoriesIds)
    {
        return $this->getEntityRepository()->getCategoriesByIds($categoriesIds);
    }

    /**
     * Provides a tree filled up to the categories provided, with all their ancestors
     * and ancestors sibligns are filled too, in order to be able to display the tree
     * directly without loading other data.
     *
     * @param CategoryInterface $root       Tree root category
     * @param Collection        $categories categories
     *
     * @return array Multi-dimensional array representing the tree
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
    {
        $parentsIds = array();

        foreach ($categories as $category) {
            $categoryParentsIds = array();
            $path = $this->getEntityRepository()->getPath($category);

            if ($path[0]->getId() === $root->getId()) {
                foreach ($path as $pathItem) {
                    $categoryParentsIds[] = $pathItem->getId();
                }
            }
            $parentsIds = array_merge($parentsIds, $categoryParentsIds);
        }
        $parentsIds = array_unique($parentsIds);

        return $this->getEntityRepository()->getTreeFromParents($parentsIds);
    }

    /**
     * Get tree by code
     *
     * @param string $code
     *
     * @return CategoryInterface
     */
    public function getTreeByCode($code)
    {
        return $this
            ->getEntityRepository()
            ->findOneBy(array('code' => $code, 'parent' => null));
    }

    /**
     * Get category by code
     *
     * @param string $code
     *
     * @return CategoryInterface
     */
    public function getCategoryByCode($code)
    {
        return $this
            ->getEntityRepository()
            ->findOneBy(array('code' => $code));
    }

    /**
     * Remove a category
     *
     * @param CategoryInterface $category
     */
    public function remove(CategoryInterface $category)
    {
        $this->eventDispatcher->dispatch(CatalogEvents::PRE_REMOVE_CATEGORY, new GenericEvent($category));

        foreach ($category->getProducts() as $product) {
            $product->removeCategory($category);
        }

        $this->getObjectManager()->remove($category);
    }

    /**
     * Move a category to another parent
     * If $prevSiblingId is provided, the category will be positioned after this
     * category, otherwise if will be the first child of the parent categpry
     *
     * @param integer $categoryId
     * @param integer $parentId
     * @param integer $prevSiblingId
     */
    public function move($categoryId, $parentId, $prevSiblingId)
    {
        $repo     = $this->getEntityRepository();
        $category = $repo->find($categoryId);
        $parent   = $repo->find($parentId);
        $prevSibling = null;

        $category->setParent($parent);

        if (!empty($prevSiblingId)) {
            $prevSibling = $repo->find($prevSiblingId);
        }

        if (is_object($prevSibling)) {
            $repo->persistAsNextSiblingOf($category, $prevSibling);
        } else {
            $repo->persistAsFirstChildOf($category, $parent);
        }
    }

    /**
     * Check if a parent node is an ancestor of a child node
     *
     * @param CategoryInterface $parentNode
     * @param CategoryInterface $childNode
     *
     * @return boolean
     */
    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode)
    {
        $childPath = $this->getEntityRepository()->getPath($childNode);
        //Removing last part of the path as it's the node itself
        //which cannot be is own ancestor
        array_pop($childPath);
        $childCount = 0;
        $parentFound = false;

        while ($childCount < count($childPath) && (!$parentFound)) {
            $parentFound = ($childPath[$childCount]->getId() === $parentNode->getId());
            $childCount++;
        }

        return $parentFound;
    }
}

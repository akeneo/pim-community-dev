<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\Classification\Factory\CategoryFactory;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Category manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Will be removed in 1.5
 */
class CategoryManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $categoryClass;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /**
     * @param ObjectManager               $om
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryFactory             $categoryFactory
     * @param string                      $categoryClass
     */
    public function __construct(
        ObjectManager $om,
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory,
        $categoryClass
    ) {
        $this->om                  = $om;
        $this->categoryRepository  = $categoryRepository;
        $this->categoryFactory     = $categoryFactory;
        $this->categoryClass       = $categoryClass;
    }

    /**
     * Return object manager
     *
     * @return ObjectManager
     *
     * @deprecated Will be removed in 1.5
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Get a new category instance
     *
     * @return CategoryInterface
     *
     * @deprecated Please use CategoryFactory::create() instead, will be removed in 1.5
     */
    public function getCategoryInstance()
    {
        return $this->categoryFactory->create();
    }

    /**
     * Return category class name (mainly used in Doctrine context)
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return string category class name
     *
     * @deprecated
     */
    public function getCategoryClass()
    {
        return $this->categoryClass;
    }

    /**
     * Return the entity repository reponsible for the category
     *
     * @return CategoryRepositoryInterface
     *
     * @deprecated Please inject "pim_catalog.repository.category" to retrieve the repository.
     */
    public function getEntityRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * Get a new tree instance
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return CategoryInterface
     *
     * @deprecated Please use CategoryFactory::create() instead
     */
    public function getTreeInstance()
    {
        return $this->categoryFactory->create();
    }

    /**
     * @return array
     *
     * @deprecated Please use CategoryRepositoryInterface::getTrees() instead
     */
    public function getTrees()
    {
        return $this->categoryRepository->getTrees();
    }

    /**
     * Get all direct children for a parent category id.
     * If the $selectNodeId is provided, all the children
     * level needed to provides the selectNode are returned
     *
     * @param int      $parentId
     * @param int|bool $selectNodeId
     *
     * @return ArrayCollection
     *
     * @deprecated Please use CategoryRepositoryInterface instead
     */
    public function getChildren($parentId, $selectNodeId = false)
    {
        $children = [];

        $entityRepository = $this->getEntityRepository();

        if ($selectNodeId === false) {
            $children = $entityRepository->getChildrenByParentId($parentId);
        } else {
            $children = $entityRepository->getChildrenTreeByParentId($parentId, $selectNodeId);
        }

        return $children;
    }

    /**
     * @return array
     *
     * @deprecated not used anymore, will be removed in 1.5
     */
    public function getTreeChoices()
    {
        $trees   = $this->getTrees();
        $choices = [];
        foreach ($trees as $tree) {
            $choices[$tree->getId()] = $tree->getLabel();
        }

        return $choices;
    }

    /**
     * Get a collection of categories based on the array of id provided
     *
     * @param array $categoriesIds
     *
     * @return Collection of categories
     *
     * @deprecated Please use CategoryRepositoryInterface::getCategoriesByIds($categoriesIds) instead
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
     *
     * TODO: To MOVE in the Repository. Try it in SQL.
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
    {
        $parentsIds = [];

        foreach ($categories as $category) {
            $categoryParentsIds = [];
            $path               = $this->getEntityRepository()->getPath($category);

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
     *
     * @deprecated not used anymore, will be removed in 1.5
     */
    public function getTreeByCode($code)
    {
        return $this
            ->getEntityRepository()
            ->findOneBy(['code' => $code, 'parent' => null]);
    }

    /**
     * Get category by code
     *
     * @param string $code
     *
     * @return CategoryInterface
     *
     * @deprecated not used anymore, will be removed in 1.5
     */
    public function getCategoryByCode($code)
    {
        return $this
            ->getEntityRepository()
            ->findOneBy(['code' => $code]);
    }

    /**
     * Move a category to another parent
     * If $prevSiblingId is provided, the category will be positioned after this
     * category, otherwise it will be the first child of the parent category
     *
     * @param int $categoryId
     * @param int $parentId
     * @param int $prevSiblingId
     *
     * @deprecated not used anymore, will be removed in 1.5
     */
    public function move($categoryId, $parentId, $prevSiblingId)
    {
        $repo        = $this->getEntityRepository();
        $category    = $repo->find($categoryId);
        $parent      = $repo->find($parentId);
        $prevSibling = null;

        $category->setParent($parent);

        if (!empty($prevSiblingId)) {
            $prevSibling = $repo->find($prevSiblingId);
        }

        // Gedmo/Tree virtual methods
        if (is_object($prevSibling)) {
            $repo->persistAsNextSiblingOf($category, $prevSibling);
        } else {
            $repo->persistAsFirstChildOf($category, $parent);
        }

        // Some persists are done in NestedTreeRepository::__call, hard to safely use a saver here
        $this->getObjectManager()->flush();
    }

    /**
     * Check if a parent node is an ancestor of a child node
     *
     * @param CategoryInterface $parentNode
     * @param CategoryInterface $childNode
     *
     * @return bool
     *
     * @deprecated not used anymore, will be removed in 1.5
     */
    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode)
    {
        return $this->getEntityRepository()->isAncestor($parentNode, $childNode);
    }
}

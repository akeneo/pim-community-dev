<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager as BaseCategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Component\Classification\Factory\CategoryFactory;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 *
 * @deprecated Will be removed in 1.5
 */
class CategoryManager extends BaseCategoryManager
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /* @var SecurityContextInterface */
    protected $securityContext;

    /** @var CategoryRepositoryInterface */
    protected $assetCategoryRepo;

    /**
     * @param ObjectManager               $om
     * @param string                      $categoryClass
     * @param EventDispatcherInterface    $eventDispatcher
     * @param CategoryAccessRepository    $categoryAccessRepo
     * @param SecurityContextInterface    $securityContext
     * @param CategoryRepositoryInterface $productCategoryRepo
     * @param CategoryRepositoryInterface $assetCategoryRepo
     * @param CategoryFactory             $categoryFactory
     */
    public function __construct(
        ObjectManager $om,
        CategoryRepositoryInterface $productCategoryRepo,
        CategoryFactory $categoryFactory,
        $categoryClass,
        EventDispatcherInterface $eventDispatcher,
        CategoryAccessRepository $categoryAccessRepo,
        SecurityContextInterface $securityContext,
        CategoryRepositoryInterface $assetCategoryRepo
    ) {
        parent::__construct($om, $productCategoryRepo, $categoryFactory, $categoryClass);

        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->securityContext    = $securityContext;
        $this->assetCategoryRepo  = $assetCategoryRepo;
    }

    /**
     * Get the trees accessible by the current user.
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return array
     */
    public function getAccessibleTrees(
        UserInterface $user,
        $accessLevel = Attributes::VIEW_PRODUCTS,
        $relatedEntity = 'product'
    ) {
        $trees = [];

        if ('product' === $relatedEntity) {
            $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $accessLevel);

            foreach ($this->productCategoryRepo->getTrees() as $tree) {
                if (in_array($tree->getId(), $grantedCategoryIds)) {
                    $trees[] = $tree;
                }
            }
        } elseif ('asset' === $relatedEntity) {
            $trees = $this->assetCategoryRepo->getTrees();
        }

        return $trees;
    }

    /**
     * Get only the granted direct children for a parent category id.
     *
     * @param int      $parentId
     * @param int|bool $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getGrantedChildren($parentId, $selectNodeId = false)
    {
        $children = $this->getChildren($parentId, $selectNodeId);
        foreach ($children as $indChild => $child) {
            $category = (is_object($child)) ? $child : $child['item'];
            if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
                unset($children[$indChild]);
            }
        }

        return $children;
    }

    /**
     * Provides a tree filled up to the categories provided, with all their ancestors
     * and ancestors sibligns are filled too, in order to be able to display the tree
     * directly without loading other data
     * We apply permissions per category to hide not granted category or branch when
     * the path is not fully granted
     *
     * @param CategoryInterface $root       Tree root category
     * @param Collection        $categories Selected categories
     *
     * @return array Multi-dimensional array representing the tree
     */
    public function getGrantedFilledTree(CategoryInterface $root, Collection $categories)
    {
        $filledTree = parent::getFilledTree($root, $categories);

        return $this->filterGrantedFilledTree($filledTree);
    }

    /**
     * Filter the filled tree to remove not granted category or branch of categories
     *
     * @param array &$filledTree the tree
     *
     * @return array Multi-dimensional array representing the tree
     */
    protected function filterGrantedFilledTree(&$filledTree)
    {
        foreach ($filledTree as $categoryIdx => &$categoryData) {
            $isLeaf = is_object($categoryData);
            $category = $isLeaf ? $categoryData : $categoryData['item'];

            if (!$this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
                unset($filledTree[$categoryIdx]);
            } elseif (!$isLeaf) {
                $this->filterGrantedFilledTree($categoryData['__children']);
            }
        }

        return $filledTree;
    }
}

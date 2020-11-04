<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Category manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 *
 * @deprecated Will be removed in 1.8
 */
class CategoryManager
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /* @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /* @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * @param CategoryRepositoryInterface   $categoryRepository
     * @param CategoryAccessRepository      $categoryAccessRepo
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get the trees accessible by the current user.
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return array
     */
    public function getAccessibleTrees(UserInterface $user, $accessLevel = Attributes::VIEW_ITEMS)
    {
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $accessLevel);

        $trees = [];
        foreach ($this->categoryRepository->getTrees() as $tree) {
            if (in_array($tree->getId(), $grantedCategoryIds)) {
                $trees[] = $tree;
            }
        }

        return $trees;
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
        $filledTree = $this->categoryRepository->getFilledTree($root, $categories);

        $this->filterGrantedFilledTree($filledTree);

        return $filledTree;
    }

    /**
     * Filter the filled tree to remove not granted category or branch of categories
     *
     * @param array $filledTree the tree
     */
    protected function filterGrantedFilledTree(&$filledTree)
    {
        foreach ($filledTree as $categoryIdx => &$categoryData) {
            $isLeaf = is_object($categoryData);
            $category = $isLeaf ? $categoryData : $categoryData['item'];

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
                unset($filledTree[$categoryIdx]);
            } elseif (!$isLeaf) {
                $this->filterGrantedFilledTree($categoryData['__children']);
            }
        }
    }
}

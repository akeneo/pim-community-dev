<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager as BaseCategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Category manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryManager extends BaseCategoryManager
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /* @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param string                   $categoryClass
     * @param EventDispatcherInterface $eventDispatcherInterface
     * @param CategoryAccessRepository $categoryAccessRepo
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ObjectManager $om,
        $categoryClass,
        EventDispatcherInterface $eventDispatcher,
        CategoryAccessRepository $categoryAccessRepo,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($om, $categoryClass, $eventDispatcher);

        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->securityContext = $securityContext;
    }

    /**
     * Get the trees accessible by the current user.
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return array
     */
    public function getAccessibleTrees(UserInterface $user, $accessLevel = CategoryVoter::VIEW_PRODUCTS)
    {
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $accessLevel);

        $trees = [];
        foreach ($this->getTrees() as $tree) {
            if (in_array($tree->getId(), $grantedCategoryIds)) {
                $trees[] = $tree;
            }
        }

        return $trees;
    }

    /**
     * Get only the granted direct children for a parent category id.
     *
     * @param integer $parentId
     * @param integer $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getGrantedChildren($parentId, $selectNodeId = false)
    {
        $children = $this->getChildren($parentId, $selectNodeId);
        foreach ($children as $indChild => $child) {
            $category = (is_object($child)) ? $child : $child['item'];
            if (false === $this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $category)) {
                unset($children[$indChild]);
            }
        }

        return $children;
    }
}

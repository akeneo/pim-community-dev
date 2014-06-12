<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager as BaseCategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryManager extends BaseCategoryManager
{
    /**
     * @var CategoryAccessRepository
     */
    protected $categoryAccessRepo;

    /**
     * Constructor
     *
     * @param ObjectManager            $om
     * @param string                   $categoryClass
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(ObjectManager $om, $categoryClass, CategoryAccessRepository $categoryAccessRepo)
    {
        parent::__construct($om, $categoryClass);

        $this->categoryAccessRepo = $categoryAccessRepo;
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
        $accessibleCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, $accessLevel);

        $trees = [];

        foreach ($this->getTrees() as $tree) {
            if (in_array($tree->getId(), $accessibleCategoryIds)) {
                $trees[] = $tree;
            }
        }

        return $trees;
    }
}

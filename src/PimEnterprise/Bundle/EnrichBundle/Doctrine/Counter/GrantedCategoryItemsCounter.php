<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Doctrine\Counter;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounter;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Granted category item counter
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedCategoryItemsCounter extends CategoryItemsCounter
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param ItemCategoryRepositoryInterface $itemRepository       Item repository
     * @param CategoryRepositoryInterface     $categoryRepository   Category repository
     * @param CategoryAccessRepository        $categoryAccessRepo   Category Access repository
     * @param AuthorizationCheckerInterface   $authorizationChecker Authorization checker
     * @param TokenStorageInterface           $tokenStorage         Token storage
     */
    public function __construct(
        ItemCategoryRepositoryInterface $itemRepository,
        CategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($itemRepository, $categoryRepository);

        $this->categoryAccessRepo   = $categoryAccessRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @see getItemsCountInCategory same logic with applying permissions
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
            return 0;
        }

        if ($inChildren) {
            $categoryIds = $this->categoryAccessRepo->getGrantedChildrenIds(
                $category,
                $this->tokenStorage->getToken()->getUser(),
                Attributes::VIEW_ITEMS
            );
        } else {
            $categoryIds = [$category->getId()];
        }

        return $this->itemRepository->getItemsCountInCategory($categoryIds);
    }
}

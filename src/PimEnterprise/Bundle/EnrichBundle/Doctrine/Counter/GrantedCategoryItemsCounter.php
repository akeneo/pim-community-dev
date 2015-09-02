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

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounter;
use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Granted category item counter
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedCategoryItemsCounter extends CategoryItemsCounter
{
    /** @var ItemCategoryRepositoryInterface */
    protected $itemRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param ItemCategoryRepositoryInterface $itemRepository       Item repository
     * @param CategoryRepositoryInterface     $categoryRepo         Category repository
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
        $this->itemRepository       = $itemRepository;
        $this->categoryRepository   = $categoryRepository;
        $this->categoryAccessRepo   = $categoryAccessRepo;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @see getItemsCountInCategory same logic with applying permissions
     */
    public function getItemsCountInCategory(
        CategoryInterface $category,
        $inChildren = false,
        $inProvided = true
    ) {
        if (false === $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
            return 0;
        }

        $grantedQb = null;
        if ($inChildren) {
            $categoryQb = $this->categoryRepository->getAllChildrenQueryBuilder($category, $inProvided);
            $grantedQb = $this->getAllGrantedChildrenQueryBuilder($categoryQb);
        }

        return $this->itemRepository->getItemsCountInCategory($category, $grantedQb);
    }

    /**
     * Build a new query builder based on children QB to let only granted children
     *
     * @param QueryBuilder $childrenQb
     *
     * @return QueryBuilder
     */
    protected function getAllGrantedChildrenQueryBuilder(QueryBuilder $childrenQb)
    {
        $categoryIds = $this->categoryAccessRepo->getGrantedCategoryIdsFromQB(
            $childrenQb,
            $this->tokenStorage->getToken()->getUser(),
            Attributes::VIEW_ITEMS
        );

        $rootAlias = current($childrenQb->getRootAliases());
        $grantedQb = $this->categoryRepository->createQueryBuilder($rootAlias);
        $grantedQb->select($rootAlias.'.id');
        $grantedQb->where($grantedQb->expr()->in($rootAlias.'.id', ':categoryIds'));
        $grantedQb->setParameter('categoryIds', $categoryIds);

        return $grantedQb;
    }
}

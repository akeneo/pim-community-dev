<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\AbstractFilter;
use Pim\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * A category filter which handles permissions
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class CategoryRightFilter extends AbstractFilter
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param CategoryAccessRepository      $categoryAccessRepo
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tokenStorage         = $tokenStorage;
        $this->categoryAccessRepo   = $categoryAccessRepo;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($categories, $type, array $options = [])
    {
        $filteredCategories = [];
        $user = $this->tokenStorage->getToken()->getUser();
        $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS);

        foreach ($categories as $key => $category) {
            if (in_array($category->getId(), $grantedCategoryIds)) {
                $filteredCategories[$key] = $category;
            }
        }

        return $filteredCategories;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($category, $type, array $options = [])
    {
        if (!$category instanceof CategoryInterface) {
            throw new \LogicException('This filter only handles objects of type "CategoryInterface"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof CategoryInterface;
    }
}

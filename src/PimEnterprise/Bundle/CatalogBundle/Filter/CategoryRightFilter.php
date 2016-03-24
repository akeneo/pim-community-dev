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

use Akeneo\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * A category filter which handles permissions
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class CategoryRightFilter extends AbstractAuthorizationFilter
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param CategoryAccessRepository      $categoryAccessRepo
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepo
    ) {
        parent::__construct($tokenStorage, $authorizationChecker);

        $this->categoryAccessRepo = $categoryAccessRepo;
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
        if (!$this->supportsObject($category, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "CategoryInterface"');
        }

        return !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof CategoryInterface;
    }
}

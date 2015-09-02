<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\FilterBundle\Filter\CategoryFilter as BaseCategoryFilter;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Override category filter to apply permissions on categories
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class CategoryFilter extends BaseCategoryFilter
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var CategoryAccessRepository */
    protected $accessRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param FormFactoryInterface          $factory              Form factory
     * @param FilterUtility                 $util                 Filter utility
     * @param CategoryRepositoryInterface   $categoryRepo
     * @param AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param CategoryAccessRepository      $accessRepo           Category access repository
     * @param TokenStorageInterface         $tokenStorage         Token storage
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        CategoryRepositoryInterface $categoryRepo,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $accessRepo,
        TokenStorageInterface $tokenStorage
    ) {
        BaseCategoryFilter::__construct($factory, $util, $categoryRepo);

        $this->authorizationChecker = $authorizationChecker;
        $this->accessRepository     = $accessRepo;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * Override to apply category permissions
     *
     * {@inheritdoc}
     */
    protected function applyFilterByAll(FilterDatasourceAdapterInterface $ds, $data)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $grantedCategoryIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS);
        if (count($grantedCategoryIds) > 0) {
            $this->util->applyFilter($ds, 'categories.id', 'IN OR UNCLASSIFIED', $grantedCategoryIds);
        } else {
            $this->util->applyFilter($ds, 'categories.id', 'UNCLASSIFIED', []);
        }

        return true;
    }

    /**
     * Override to apply category permissions
     *
     * {@inheritdoc}
     */
    protected function applyFilterByUnclassified(FilterDatasourceAdapterInterface $ds, $data)
    {
        $tree = $this->categoryRepo->find($data['treeId']);
        if ($tree) {
            // all categories of this tree (without permissions)
            $currentTreeIds = $this->categoryRepo->getAllChildrenIds($tree);
            $this->util->applyFilter($ds, 'categories.id', 'NOT IN', $currentTreeIds);

            // we add a filter on granted categories
            $user = $this->tokenStorage->getToken()->getUser();
            $grantedIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS);
            $this->util->applyFilter($ds, 'categories.id', 'IN OR UNCLASSIFIED', $grantedIds);

            return true;
        }

        return false;
    }

    /**
     * Override to apply category permissions
     *
     * {@inheritdoc}
     */
    protected function getAllChildrenIds(CategoryInterface $category)
    {
        if ($category instanceof Category) { // TODO: Remove this first if in PIM-4292
            if (false === $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
                return [];
            }
        }

        $childrenIds = parent::getAllChildrenIds($category);

        $user = $this->tokenStorage->getToken()->getUser();
        $grantedIds = $this->accessRepository->getCategoryIdsWithExistingAccess(
            $user->getGroups()->toArray(),
            $childrenIds
        );

        return $grantedIds;
    }
}

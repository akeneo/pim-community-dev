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
use Pim\Bundle\FilterBundle\Filter\CategoryFilter as BaseCategoryFilter;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Override category filter to apply permissions on categories
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class CategoryFilter extends BaseCategoryFilter
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var CategoryAccessRepository */
    protected $accessRepository;

    /**
     * @param FormFactoryInterface        $factory         Form factory
     * @param FilterUtility               $util            Filter utility
     * @param CategoryRepositoryInterface $categoryRepo
     * @param SecurityContextInterface    $securityContext Security context
     * @param CategoryAccessRepository    $accessRepo      Category access repository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        CategoryRepositoryInterface $categoryRepo,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $accessRepo
    ) {
        BaseCategoryFilter::__construct($factory, $util, $categoryRepo);

        $this->securityContext = $securityContext;
        $this->accessRepository = $accessRepo;
    }

    /**
     * Override to apply category permissions
     *
     * {@inheritdoc}
     */
    protected function applyFilterByAll(FilterDatasourceAdapterInterface $ds, $data)
    {
        $user = $this->securityContext->getToken()->getUser();
        $grantedCategoryIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS);
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
            $user = $this->securityContext->getToken()->getUser();
            $grantedIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS);
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
        if (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
            return [];
        }

        $childrenIds = BaseCategoryFilter::getAllChildrenIds($category);

        $user = $this->securityContext->getToken()->getUser();
        $grantedIds = $this->accessRepository->getCategoryIdsWithExistingAccess(
            $user->getGroups()->toArray(),
            $childrenIds
        );

        return $grantedIds;
    }
}

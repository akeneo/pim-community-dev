<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\FilterBundle\Filter\Product\CategoryFilter as BaseCategoryFilter;
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
     * Constructor
     *
     * @param FormFactoryInterface     $factory         Form factory
     * @param FilterUtility            $util            Filter utility
     * @param ProductCategoryManager   $manager         Product category manager
     * @param SecurityContextInterface $securityContext Security context
     * @param CategoryAccessRepository $accessRepo      Category access repository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        ProductCategoryManager $manager,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $accessRepo
    ) {
        parent::__construct($factory, $util, $manager);

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
        $qb = $ds->getQueryBuilder();
        $user = $this->securityContext->getToken()->getUser();
        $grantedCategoryIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS);
        $productRepository = $this->manager->getProductCategoryRepository();

        if (count($grantedCategoryIds) > 0) {
            $productRepository->applyFilterByCategoryIdsOrUnclassified($qb, $grantedCategoryIds);
        } else {
            $productRepository->applyFilterByUnclassified($qb);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterByUnclassified(FilterDatasourceAdapterInterface $ds, $data)
    {
        $this->applyFilterByAll($ds, $data);

        return parent::applyFilterByUnclassified($ds, $data);
    }

    /**
     * Override to apply category permissions (not for unclassified)
     *
     * {@inheritdoc}
     */
    protected function getProductIdsInCategory(CategoryInterface $category, $data)
    {
        if ($data['categoryId'] === self::UNCLASSIFIED_CATEGORY) {
            $productIds = $this->manager->getProductIdsInCategory($category, $data['includeSub']);
        } else {
            $productIds = $this->manager->getProductIdsInGrantedCategory($category, $data['includeSub']);
        }

        return (empty($productIds)) ? [0] : $productIds;
    }
}

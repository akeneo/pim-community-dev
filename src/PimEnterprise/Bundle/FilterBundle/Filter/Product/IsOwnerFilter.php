<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use PimEnterprise\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;

/**
 * Is owner of products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class IsOwnerFilter extends OroChoiceFilter
{
    /**
     * @var ProductCategoryRepositoryInterface $repository
     */
    protected $categoryRepository;

    /**
     * @var CategoryAccessRepository $repository
     */
    protected $accessRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface               $factory
     * @param FilterUtility                      $util
     * @param ProductCategoryRepositoryInterface $categoryRepository
     * @param CategoryAccessRepository           $accessRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        ProductCategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $accessRepository
    ) {
        parent::__construct($factory, $util);
        $this->categoryRepository = $categoryRepository;
        $this->accessRepository = $accessRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {

        return true;
    }
}

<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use PimEnterprise\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Is owner of products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class IsOwnerFilter extends OroChoiceFilter
{
    /* @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductCategoryRepositoryInterface $repository */
    protected $categoryRepository;

    /** @var CategoryAccessRepository $repository */
    protected $accessRepository;

    /**
     * Constructor
     *
     * @param FormFactoryInterface               $factory
     * @param FilterUtility                      $util
     * @param SecurityContextInterface           $securityContext
     * @param ProductCategoryRepositoryInterface $categoryRepository
     * @param CategoryAccessRepository           $accessRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        SecurityContextInterface $securityContext,
        ProductCategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $accessRepository
    ) {
        parent::__construct($factory, $util);
        $this->securityContext = $securityContext;
        $this->categoryRepository = $categoryRepository;
        $this->accessRepository = $accessRepository;
    }

    /**
     * Filter by owner category ids (category with owner permissions or not classified at all)
     *
     * @return boolean
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $user = $this->securityContext->getToken()->getUser();
        $grantedCategoryIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::OWN_PRODUCTS);
        $grantedCategoryIds = count($grantedCategoryIds) > 0 ? $grantedCategoryIds : [-1];

        $qb = $ds->getQueryBuilder();
        $this->categoryRepository->addFilterByAll($qb, $grantedCategoryIds /*, 'owner'*/);

        // TODO : IN / NOT IN
        return true;
    }
}

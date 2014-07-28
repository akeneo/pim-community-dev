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
    protected $productRepository;

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
        ProductCategoryRepositoryInterface $productRepository,
        CategoryAccessRepository $accessRepository
    ) {
        parent::__construct($factory, $util);
        $this->securityContext = $securityContext;
        $this->productRepository = $productRepository;
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

        $qb = $ds->getQueryBuilder();
        if ($data['value'] === 1) {
            $grantedCategoryIds = $this->accessRepository->getGrantedCategoryIds($user, Attributes::OWN_PRODUCTS);
            if (count($grantedCategoryIds > 0)) {
                $this->productRepository->applyFilterByCategoryIdsOrUnclassified($qb, $grantedCategoryIds, true);
            } else {
                $this->productRepository->applyFilterByUnclassified($qb);
            }
        }

        return true;
    }
}

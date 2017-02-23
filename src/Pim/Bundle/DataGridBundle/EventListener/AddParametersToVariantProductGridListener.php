<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToVariantProductGridListener extends AddParametersToProductGridListener
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param array                      $paramNames     Parameter name that should be binded to query
     * @param RequestParameters          $requestParams  Request params
     * @param CatalogContext             $catalogContext The catalog context
     * @param UserContext                $userContext    User context
     * @param ProductRepositoryInterface $productRepository The product manager
     * @param bool                       $isEditMode     Whether or not to add data_in, data_not_in params to query
     */
    public function __construct(
        $paramNames,
        RequestParameters $requestParams,
        CatalogContext $catalogContext,
        UserContext $userContext,
        ProductRepositoryInterface $productRepository,
        $isEditMode = false
    ) {
        parent::__construct($paramNames, $requestParams, $catalogContext, $userContext, $isEditMode);
        $this->productRepository = $productRepository;
    }

    /**
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = parent::prepareParameters();

        $variantGroupId = $queryParameters['currentGroup'];

        if (null !== $variantGroupId) {
            $products = $this->productRepository->getEligibleProductsForVariantGroup($variantGroupId);
            $productIds = [];
            foreach ($products as $product) {
                $productIds[] = $product->getId();
            }

            if (count($productIds) === 0) {
                $productIds = [0];
            }
        } else {
            $productIds = [0];
        }

        // TODO - TIP-664: make the datagrid work with ES
//        $queryParameters['productIds'] = $productIds;

        return $queryParameters;
    }
}

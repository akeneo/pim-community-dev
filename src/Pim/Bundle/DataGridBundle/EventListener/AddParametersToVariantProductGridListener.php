<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToVariantProductGridListener extends AddParametersToProductGridListener
{
    /** @var ProductManager */
    protected $productManager;

    /**
     * @param array             $paramNames     Parameter name that should be binded to query
     * @param RequestParameters $requestParams  Request params
     * @param CatalogContext    $catalogContext The catalog context
     * @param UserContext       $userContext    User context
     * @param ProductManager    $productManager The product manager
     * @param boolean           $isEditMode     Whether or not to add data_in, data_not_in params to query
     */
    public function __construct(
        $paramNames,
        RequestParameters $requestParams,
        CatalogContext $catalogContext,
        UserContext $userContext,
        ProductManager $productManager,
        $isEditMode = false
    ) {
        parent::__construct($paramNames, $requestParams, $catalogContext, $userContext, $isEditMode);
        $this->productManager = $productManager;
    }

    /**
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = parent::prepareParameters();

        $variantGroupId = $queryParameters['currentGroup'];

        if (null !== $variantGroupId) {
            $productIds = $this->productManager
                ->getProductRepository()
                ->getEligibleProductIdsForVariantGroup($variantGroupId);
            if (count($productIds) === 0) {
                $productIds = array(0);
            }
        } else {
            $productIds = [0];
        }

        $queryParameters['productIds'] = $productIds;

        return $queryParameters;
    }
}

<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToProductGridListener extends AddParametersToGridListener
{
    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @param array             $paramNames     Parameter name that should be binded to query
     * @param RequestParameters $requestParams  Request params
     * @param ProductManager    $productManager Product manager
     * @param UserContext       $userContext    User context
     * @param boolean           $isEditMode     Whether or not to add data_in, data_not_in params to query
    */
    public function __construct(
        $paramNames,
        RequestParameters $requestParams,
        ProductManager $productManager,
        UserContext $userContext,
        $isEditMode = false
    ) {
        parent::__construct($paramNames, $requestParams, $isEditMode);

        $this->productManager = $productManager;
        $this->userContext    = $userContext;
    }

    /**
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = parent::prepareParameters();

        $dataLocale = $this->getLocale($queryParameters);
        $this->productManager->setLocale($dataLocale);

        $dataScope = $this->getScope();
        $queryParameters['scopeCode'] = $dataScope;

        return $queryParameters;
    }

    /**
     * @param array $queryParameters
     *
     * @return string
     */
    protected function getLocale($queryParameters)
    {
        $dataLocale = null;
        if (isset($queryParameters['dataLocale'])) {
            $dataLocale = $queryParameters['dataLocale'];
        }
        if ($dataLocale == null) {
            $dataLocale = $this->userContext->getUserLocale()->getCode();
        }

        return $dataLocale;
    }

    /**
     * @return string
     */
    protected function getScope()
    {
        $filterValues = $this->requestParams->get('_filter');
        if (isset($filterValues['scope']['value']) && $filterValues['scope']['value'] !== null) {
            return $filterValues['scope']['value'];
        } else {
            return $this->userContext->getUserChannelCode();
        }
    }
}

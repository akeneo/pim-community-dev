<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToProductGridListener extends AddParametersToGridListener
{
    /** @var UserContext */
    protected $userContext;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param array             $paramNames     Parameter name that should be binded to query
     * @param RequestParameters $requestParams  Request params
     * @param CatalogContext    $catalogContext The catalog context
     * @param UserContext       $userContext    User context
     * @param bool              $isEditMode     Whether or not to add data_in, data_not_in params to query
     * @param RequestStack      $requestStack   Request
     */
    public function __construct(
        $paramNames,
        RequestParameters $requestParams,
        CatalogContext $catalogContext,
        UserContext $userContext,
        $isEditMode = false,
        RequestStack $requestStack
    ) {
        parent::__construct($paramNames, $requestParams, $isEditMode);

        $this->catalogContext = $catalogContext;
        $this->userContext = $userContext;
        $this->requestStack = $requestStack;
    }

    /**
     * @return null|Request
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = parent::prepareParameters();

        $dataLocale = $this->getLocale($queryParameters);
        $queryParameters['dataLocale'] = $dataLocale;
        // TODO : strange that we need to set it here, would expect from the datasource
        $this->catalogContext->setLocaleCode($dataLocale);

        $dataScope = $this->getScope();
        $queryParameters['scopeCode'] = $dataScope;

        return $queryParameters;
    }

    /**
     * Get datalocale from parent's parameters, fallback on request parameters to deal with the mass edit case
     *
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
        if (null === $dataLocale) {
            $dataLocale = $this->getRequest()->get('dataLocale', null);
        }
        if (null === $dataLocale) {
            $dataLocale = $this->userContext->getCurrentLocaleCode();
        }

        return $dataLocale;
    }

    /**
     * Get scope from datagrid's filters, fallback on request parameters to deal with the mass edit case
     *
     * @return string
     */
    protected function getScope()
    {
        $filterValues = $this->requestParams->get('_filter');
        if (empty($filterValues)) {
            $filterValues = $this->getRequest()->get('filters');
            if (is_string($filterValues)) {
                $filterValues = json_decode($filterValues, true);
            }
            if (!$filterValues) {
                $filterValues = [];
            }
        }

        if (isset($filterValues['scope']['value']) && $filterValues['scope']['value'] !== null) {
            return $filterValues['scope']['value'];
        } else {
            return $this->userContext->getUserChannelCode();
        }
    }
}

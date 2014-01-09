<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToProductGridListener
{
    /** @var array */
    protected $paramNames;

    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @param array             $paramNames     Parameter name that should be binded to query
     * @param RequestParameters $requestParams  Request params
     * @param ProductManager    $productManager Product manager
     * @param LocaleManager     $localeManager  Locale manager
     * @param ChannelManager    $channelManager Channel manager
     */
    public function __construct(
        $paramNames,
        RequestParameters $requestParams,
        ProductManager $productManager,
        LocaleManager $localeManager,
        ChannelManager $channelManager
    ) {
        $this->paramNames     = $paramNames;
        $this->requestParams  = $requestParams;
        $this->productManager = $productManager;
        $this->localeManager  = $localeManager;
        $this->channelManager = $channelManager;
    }

    /**
     * Bound parameters in query builder
    *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();
            $queryParameters = array();
            foreach ($this->paramNames as $paramName) {
                $queryParameters[$paramName] = $this->requestParams->get($paramName, null);
            }
            if (isset($queryParameters['dataLocale'])) {

                $dataLocale = $queryParameters['dataLocale'];
                if ($dataLocale == null) {
                    $dataLocale = $this->localeManager->getUserLocale()->getCode();
                }
                $this->productManager->setLocale($dataLocale);

                $filterValues = $this->requestParams->get('_filter');
                if (isset($filterValues['scope']['value']) && $filterValues['scope']['value'] != null) {
                    $queryParameters['scopeCode'] = $filterValues['scope']['value'];
                } else {
                    $queryParameters['scopeCode'] = $this->channelManager->getUserChannelCode();
                }
            }

            $queryBuilder->setParameters($queryParameters);
        }
    }
}

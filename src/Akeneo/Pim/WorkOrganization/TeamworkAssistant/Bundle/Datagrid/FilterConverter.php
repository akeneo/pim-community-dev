<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ProductProposalDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface as PimDatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Filter\FilterExtension;
use Webmozart\Assert\Assert;

/**
 * It crafts a fake request in order to add parameters needed to convert oro grid filters into PQB filters.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FilterConverter
{
    /** @var RequestParameters */
    private $requestParams;

    /** @var ManagerInterface */
    private $manager;

    /**
     * @param RequestParameters $requestParams
     * @param ManagerInterface  $manager
     */
    public function __construct(
        RequestParameters $requestParams,
        ManagerInterface $manager
    ) {
        $this->requestParams = $requestParams;
        $this->manager = $manager;
    }

    /**
     * It converts oro grid filters into PQB filters.
     *
     * @param array $filters
     *
     * @return array
     */
    public function convert($filters)
    {
        // as the manager reset the state of the parameters, we have to initialize it first
        // and then set the filters to be handled by \Akeneo\Pim\Permission\Bundle\Datagrid\Product\SelectedAttributesConfigurator
        $this->requestParams->setRootParameter(OroToPimGridFilterAdapter::PRODUCT_GRID_NAME);
        $this->requestParams->set(FilterExtension::FILTER_ROOT_PARAM, $filters);

        // initialize the datagrid with the filters such as category permissions
        $datagrid = $this->manager->getDatagrid(OroToPimGridFilterAdapter::PRODUCT_GRID_NAME);

        // trigger the build of the datagrid with the attribute filters
        $acceptedDatasource = $datagrid->getAcceptedDatasource();
        Assert::implementsInterface($acceptedDatasource, PimDatasourceInterface::class);
        $acceptedDatasource->getQueryBuilder();

        $datasource = $datagrid->getDatasource();
        if (!$datasource instanceof ProductDatasource
            && !$datasource instanceof ProductAndProductModelDatasource
            && !$datasource instanceof ProductProposalDatasource
        ) {
            throw new \InvalidArgumentException('Datasource must be a product datasource.');
        }

        $filters = $datasource->getProductQueryBuilder()->getRawFilters();

        return $filters;
    }
}

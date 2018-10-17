<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Filter\FilterExtension;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductAndProductModelQueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FilterConverterSpec extends ObjectBehavior
{
    function let(RequestParameters $requestParameters, ManagerInterface $manager) {
        $this->beConstructedWith($requestParameters, $manager);
    }

    function it_converts_datagrid_filters_into_pqb_filters(
        $requestParameters,
        $manager,
        DatagridInterface $datagrid,
        ProductDatasource $datasource,
        ProductAndProductModelQueryBuilder $queryBuilder
    ) {
        $requestParameters->setRootParameter(OroToPimGridFilterAdapter::PRODUCT_GRID_NAME)->shouldBeCalled();
        $requestParameters->set(FilterExtension::FILTER_ROOT_PARAM, ['name' => 'value'])->shouldBeCalled();

        $manager->getDatagrid(OroToPimGridFilterAdapter::PRODUCT_GRID_NAME)->willReturn($datagrid);

        // trigger the build of the datagrid with the attribute filters
        $datagrid->getAcceptedDatasource()->willReturn($datasource);
        $datasource->getQueryBuilder()->shouldBeCalled();

        $filters = $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getProductQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->getRawFilters()->willReturn(['name' => 'value']);

        $this->convert(['name' => 'value']);
    }
}

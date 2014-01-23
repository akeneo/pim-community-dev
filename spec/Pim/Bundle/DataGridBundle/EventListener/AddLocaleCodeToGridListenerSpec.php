<?php

namespace spec\Pim\Bundle\DataGridBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

class AddLocaleCodeToGridListenerSpec extends ObjectBehavior
{
    function let(RequestParameters $requestParams)
    {
        $this->beConstructedWith($requestParams);
    }

    function it_adds_locale_parameter_to_query_builder(
        BuildAfter $event,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $config,
        OrmDatasource $datasource,
        QueryBuilder $queryBuilder,
        $requestParams
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $acceptor->getConfig()->willReturn($config);
        $config->offsetGetByPath('[options][locale_parameter]')->willReturn('dataLocale');
        $datasource->getQueryBuilder()->willReturn($queryBuilder);
        $requestParams->get('dataLocale', null)->willReturn('fr_FR');

        $queryBuilder->setParameter('dataLocale', 'fr_FR');

        $this->onBuildAfter($event);
    }

    function it_does_nothing_when_locale_parameter_is_not_set(
        BuildAfter $event,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $config,
        OrmDatasource $datasource,
        $requestParams
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $acceptor->getConfig()->willReturn($config);
        $config->offsetGetByPath('[options][locale_parameter]')->willReturn(null);

        $requestParams->get(Argument::cetera())->shouldNotBeCalled();

        $this->onBuildAfter($event);
    }

    function it_does_nothing_when_datasource_is_not_an_orm_datasource(
        BuildAfter $event,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $config,
        DatasourceInterface $datasource,
        $requestParams
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $acceptor->getConfig()->willReturn($config);
        $config->offsetGetByPath('[options][locale_parameter]')->willReturn(null);

        $requestParams->get(Argument::cetera())->shouldNotBeCalled();

        $this->onBuildAfter($event);
    }
}

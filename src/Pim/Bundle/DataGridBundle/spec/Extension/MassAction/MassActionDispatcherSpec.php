<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionExtension;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerRegistry;
use Pim\Component\Catalog\Repository\ProductMassActionRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class MassActionDispatcherSpec extends ObjectBehavior
{
    function let(
        MassActionHandlerRegistry $handlerRegistry,
        ManagerInterface $manager,
        RequestParameters $requestParams,
        MassActionParametersParser $parametersParser,
        DatagridInterface $grid,
        Acceptor $acceptor,
        DatasourceInterface $acceptedDatasource,
        DatasourceInterface $datasource,
        QueryBuilder $queryBuilder
    ) {
        $this->beConstructedWith($handlerRegistry, $manager, $requestParams, $parametersParser);

        $acceptedDatasource->getQueryBuilder()->willReturn($queryBuilder);
        $grid->getAcceptor()->willReturn($acceptor);
        $grid->getAcceptedDatasource()->willReturn($acceptedDatasource);
        $grid->getDatasource()->willReturn($datasource);
        $manager->getDatagrid('grid')->willReturn($grid);
    }

    function it_returns_mass_action(
        $handlerRegistry,
        DatagridInterface $grid,
        Acceptor $acceptor,
        MassActionExtension $massActionExtension,
        MassActionInterface $massActionInterface,
        QueryBuilder $queryBuilder,
        DatasourceInterface $datasource,
        ProductMassActionRepositoryInterface $massActionRepository,
        MassActionHandlerInterface $massActionHandler,
        $parametersParser
    ) {
        $request = new Request([
            'inset'      => 'inset',
            'values'     => 1,
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => 'mass_edit_action',
        ]);

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => 1]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $massActionRepository->applyMassActionParameters($queryBuilder, 'inset', 1)->willReturn(null);
        $massActionExtension->getMassAction('mass_edit_action', $grid)->willReturn($massActionInterface);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);

        $alias = 'mass_action_alias';
        $options = new ArrayCollection();
        $options->offsetSet('handler', $alias);
        $massActionInterface->getOptions()->willReturn($options);
        $handlerRegistry->getHandler($alias)->willReturn($massActionHandler);
        $massActionHandler->handle($grid, $massActionInterface)->willReturn($massActionHandler);

        $this->dispatch($request)->shouldReturnAnInstanceOf('\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface');
    }

    function it_gets_the_values_from_the_request_form_data(
        $handlerRegistry,
        $parametersParser,
        DatagridInterface $grid,
        Acceptor $acceptor,
        MassActionExtension $massActionExtension,
        MassActionInterface $massActionInterface,
        QueryBuilder $queryBuilder,
        DatasourceInterface $datasource,
        ProductMassActionRepositoryInterface $massActionRepository,
        MassActionHandlerInterface $massActionHandler
    ) {
        $request = new Request(
            [
                'inset'      => 'inset',
                'values'     => [],
                'gridName'   => 'grid',
                'massAction' => $massActionInterface,
                'actionName' => 'mass_edit_action',
            ],
            [
                'productIds' => 'id_1,id_2',
            ]
        );

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => []]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $massActionRepository->applyMassActionParameters($queryBuilder, 'inset', ['id_1', 'id_2'])->willReturn(null);
        $massActionExtension->getMassAction('mass_edit_action', $grid)->willReturn($massActionInterface);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);

        $alias = 'mass_action_alias';
        $options = new ArrayCollection();
        $options->offsetSet('handler', $alias);
        $massActionInterface->getOptions()->willReturn($options);
        $handlerRegistry->getHandler($alias)->willReturn($massActionHandler);
        $massActionHandler->handle($grid, $massActionInterface)->willReturn($massActionHandler);

        $this->dispatch($request)->shouldReturnAnInstanceOf('\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface');
    }

    function it_gets_the_values_from_the_url_parameter_when_the_values_in_the_form_data_are_empty(
        $handlerRegistry,
        $parametersParser,
        DatagridInterface $grid,
        Acceptor $acceptor,
        MassActionExtension $massActionExtension,
        MassActionInterface $massActionInterface,
        QueryBuilder $queryBuilder,
        DatasourceInterface $datasource,
        ProductMassActionRepositoryInterface $massActionRepository,
        MassActionHandlerInterface $massActionHandler
    ) {
        $postParameters = [];
        $request = new Request(
            [
                'inset'      => 'inset',
                'values'     => 1,
                'gridName'   => 'grid',
                'massAction' => $massActionInterface,
                'actionName' => 'mass_edit_action',
            ],
            $postParameters
        );

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => 1]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $massActionRepository->applyMassActionParameters($queryBuilder, 'inset', 1)->willReturn(null);
        $massActionExtension->getMassAction('mass_edit_action', $grid)->willReturn($massActionInterface);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);

        $alias = 'mass_action_alias';
        $options = new ArrayCollection();
        $options->offsetSet('handler', $alias);
        $massActionInterface->getOptions()->willReturn($options);
        $handlerRegistry->getHandler($alias)->willReturn($massActionHandler);
        $massActionHandler->handle($grid, $massActionInterface)->willReturn($massActionHandler);

        $this->dispatch($request)->shouldReturnAnInstanceOf('\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface');
    }

    function it_throws_an_exception_without_extension($parametersParser, Acceptor $acceptor)
    {
        $request = new Request([
            'inset'      => 'inset',
            'values'     => 1,
            'gridName'   => 'grid',
            'actionName' => 'mass_edit_action',
        ]);

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => 1]);
        $acceptor->getExtensions()->willReturn([]);

        $this->shouldThrow(new \LogicException("MassAction extension is not applied to datagrid."))
            ->during('dispatch', [$request]);
    }

    function it_throws_an_exception_with_not_found_mass_action(
        $parametersParser,
        DatagridInterface $grid,
        Acceptor $acceptor,
        MassActionExtension $massActionExtension,
        MassActionInterface $massActionInterface
    ) {
        $massActionName = 'mass_edit_action';
        $request = new Request([
            'inset'      => 'inset',
            'values'     => 1,
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName,
        ]);

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => 1]);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);
        $massActionExtension->getMassAction($massActionName, $grid)->willReturn(false);

        $this->shouldThrow(new \LogicException(sprintf('Can\'t find mass action "%s"', $massActionName)))
            ->during('dispatch', [$request]);
    }

    function it_throws_an_exception_without_values($parametersParser)
    {
        $massActionName = 'mass_edit_action';
        $request = new Request([
            'actionName' => $massActionName,
        ]);

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => '']);

        $this->shouldThrow(new \LogicException(sprintf('There is nothing to do in mass action "%s"', $massActionName)))
            ->during('dispatch', [$request]);
    }

    function it_throws_an_exception_if_datasource_is_not_an_instance_of_productdatasource(
        $parametersParser,
        DatagridInterface $grid,
        Acceptor $acceptor,
        MassActionExtension $massActionExtension,
        MassActionInterface $massActionInterface,
        DatasourceInterface $datasource,
        ProductMassActionRepositoryInterface $massActionRepository
    ) {
        $massActionName = 'mass_edit_action';
        $request = new Request([
            'inset'      => 'inset',
            'values'     => 1,
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName,
        ]);

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => 1]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $massActionExtension->getMassAction($massActionName, $grid)->willReturn($massActionInterface);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);

        $this->shouldThrow(new \LogicException('getRawFilters is only implemented for ProductDatasource'))
            ->during('getRawFilters', [$request]);
    }
}

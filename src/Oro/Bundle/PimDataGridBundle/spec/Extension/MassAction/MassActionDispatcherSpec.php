<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction;

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
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionHandlerRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface;
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
        $this->beConstructedWith($handlerRegistry, $manager, $requestParams, $parametersParser, ['product-grid']);

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
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => 'mass_edit_action',
        ]);

        $parametersParser->parse($request)->willReturn([
            'inset' => 'inset',
            'values' => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => 'mass_edit_action',
        ]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $massActionRepository->applyMassActionParameters($queryBuilder, 'inset', [1])->willReturn(null);
        $massActionExtension->getMassAction('mass_edit_action', $grid)->willReturn($massActionInterface);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);

        $alias = 'mass_action_alias';
        $options = new ArrayCollection();
        $options->offsetSet('handler', $alias);
        $massActionInterface->getOptions()->willReturn($options);
        $handlerRegistry->getHandler($alias)->willReturn($massActionHandler);
        $massActionHandler->handle($grid, $massActionInterface)->willReturn($massActionHandler);

        $this->dispatch([
            'inset' => 'inset',
            'values' => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => 'mass_edit_action',
        ])->shouldReturnAnInstanceOf('\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface');
    }

    function it_throws_an_exception_without_extension(
        $parametersParser,
        $datasource,
        Acceptor $acceptor,
        ProductMassActionRepositoryInterface $massActionRepository
    ) {
        $request = new Request([
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'actionName' => 'mass_edit_action',
        ]);

        $parametersParser->parse($request)->willReturn([
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'actionName' => 'mass_edit_action']);
        $acceptor->getExtensions()->willReturn([]);

        $datasource->getMassActionRepository()->willReturn($massActionRepository);

        $this->shouldThrow(new \LogicException("MassAction extension is not applied to datagrid."))
            ->during('dispatch', [[
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'actionName' => 'mass_edit_action'
        ]]);
    }

    function it_throws_an_exception_when_the_mass_action_does_not_exist(
        $parametersParser,
        $datasource,
        DatagridInterface $grid,
        Acceptor $acceptor,
        MassActionExtension $massActionExtension,
        MassActionInterface $massActionInterface,
        ProductMassActionRepositoryInterface $massActionRepository
    ) {
        $massActionName = 'mass_edit_action';
        $request = new Request([
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName,
        ]);

        $parametersParser->parse($request)->willReturn([
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName
        ]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);
        $massActionExtension->getMassAction($massActionName, $grid)->willReturn(false);

        $this->shouldThrow(new \LogicException(sprintf('Can\'t find mass action "%s"', $massActionName)))
            ->during('dispatch', [[
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName
        ]]);
    }

    function it_throws_an_exception_without_values($parametersParser)
    {
        $massActionName = 'mass_edit_action';
        $request = new Request([
            'actionName' => $massActionName,
        ]);

        $parametersParser->parse($request)->willReturn(['inset' => 'inset', 'values' => '']);

        $this->shouldThrow(new \LogicException(sprintf('There is nothing to do in mass action "%s"', $massActionName)))
            ->during('dispatch', [['inset' => 'inset', 'values' => '', 'actionName' => $massActionName]]);
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
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName,
        ]);

        $parametersParser->parse($request)->willReturn([
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName
        ]);
        $datasource->getMassActionRepository()->willReturn($massActionRepository);
        $massActionExtension->getMassAction($massActionName, $grid)->willReturn($massActionInterface);
        $acceptor->getExtensions()->willReturn([$massActionExtension]);

        $this->shouldThrow(new \LogicException('getRawFilters is only implemented for ProductDatasource and ProductAndProductModelDatasource'))
            ->during('getRawFilters', [[
            'inset'      => 'inset',
            'values'     => [1],
            'gridName'   => 'grid',
            'massAction' => $massActionInterface,
            'actionName' => $massActionName
        ]]);
    }
}

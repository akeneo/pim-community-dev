<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;

class DatasourceSpec extends ObjectBehavior
{
    function let(ObjectManager $manager, HydratorInterface $hydrator)
    {
        $this->beConstructedWith($manager, $hydrator);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement(DatasourceInterface::class);
    }

    function it_processes_a_datasource_with_repository_configuration(
        $manager,
        DatagridInterface $grid,
        GroupRepositoryInterface $repository
    ) {
        $config = [
            'repository_method' => 'createAssociationDatagridQueryBuilder',
            'entity'            => 'Group'
        ];
        $manager->getRepository('Group')->willReturn($repository);
        $repository->createAssociationDatagridQueryBuilder([])->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_processes_a_datasource_with_repository_configuration_and_parameters(
        $manager,
        DatagridInterface $grid,
        GroupRepositoryInterface $repository
    ) {
        $config = [
            'repository_method'     => 'createAssociationDatagridQueryBuilder',
            'repository_parameters' => ['locale' => 'fr_FR'],
            'entity'                => 'Group'
        ];
        $manager->getRepository('Group')->willReturn($repository);
        $repository->createAssociationDatagridQueryBuilder(['locale' => 'fr_FR'])->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_throws_exception_when_process_with_missing_configuration(DatagridInterface $grid)
    {
        $config = [
            'repository_method' => 'createAssociationDatagridQueryBuilder',
        ];

        $this
            ->shouldThrow(
                new \Exception(
                    '"Oro\Bundle\PimDataGridBundle\Datasource\Datasource" expects to be configured with "entity"'
                )
            )
            ->duringProcess($grid, $config);
    }
}

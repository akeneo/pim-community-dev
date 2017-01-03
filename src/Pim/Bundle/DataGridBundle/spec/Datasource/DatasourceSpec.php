<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Component\Catalog\Repository\ProductMassActionRepositoryInterface;

class DatasourceSpec extends ObjectBehavior
{
    function let(ObjectManager $manager, HydratorInterface $hydrator, ProductMassActionRepositoryInterface $massActionRepo, ProductQueryBuilderFactoryInterface $factory)
    {
        $this->beConstructedWith($manager, $hydrator, $massActionRepo, $factory);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface');
    }

    function it_processes_a_datasource_with_repository_configuration(
        $manager,
        DatagridInterface $grid,
        ProductRepository $repository
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Product'
        ];
        $manager->getRepository('Product')->willReturn($repository);
        $repository->createDatagridQueryBuilder([])->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_processes_a_datasource_with_repository_configuration_and_parameters(
        $manager,
        DatagridInterface $grid,
        ProductRepository $repository
    ) {
        $config = [
            'repository_method'     => 'createDatagridQueryBuilder',
            'repository_parameters' => ['locale' => 'fr_FR'],
            'entity'                => 'Product'
        ];
        $manager->getRepository('Product')->willReturn($repository);
        $repository->createDatagridQueryBuilder(['locale' => 'fr_FR'])->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_processes_a_datasource_with_default_query_builder(
        $manager,
        DatagridInterface $grid,
        ProductRepository $repository
    ) {
        $config = [
            'entity' => 'Product'
        ];
        $manager->getRepository('Product')->willReturn($repository);
        $repository->createQueryBuilder([])->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_throws_exception_when_process_with_missing_configuration(
        $manager,
        DatagridInterface $grid,
        ProductRepository $repository
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
        ];

        $this
            ->shouldThrow(
                new \Exception(
                    '"Pim\Bundle\DataGridBundle\Datasource\Datasource" expects to be configured with "entity"'
                )
            )
            ->duringProcess($grid, $config);
    }
}

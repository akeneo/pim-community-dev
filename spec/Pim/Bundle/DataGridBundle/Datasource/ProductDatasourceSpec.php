<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository;

class ProductDatasourceSpec extends ObjectBehavior
{
    function let(ObjectManager $manager, HydratorInterface $hydrator)
    {
        $this->beConstructedWith($manager, $hydrator);
    }

    function it_is_a_datasource()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface');
    }

    function it_processes_a_datasource_with_repository_configuration(ObjectManager $manager, DatagridInterface $grid, ProductRepository $repository)
    {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Product'
        ];
        $manager->getRepository('Product')->willReturn($repository);
        $repository->createDatagridQueryBuilder()->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_processes_a_datasource_with_repository_configuration_and_parameters(ObjectManager $manager, DatagridInterface $grid, ProductRepository $repository)
    {
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

    function it_processes_a_datasource_with_default_query_builder(ObjectManager $manager, DatagridInterface $grid, ProductRepository $repository)
    {
        $config = [
            'entity'            => 'Product'
        ];
        $manager->getRepository('Product')->willReturn($repository);
        $repository->createQueryBuilder('o')->shouldBeCalled();
        $grid->setDatasource($this)->shouldBeCalled();
        $this->process($grid, $config);
    }

    function it_throws_exception_when_process_with_missing_configuration(ObjectManager $manager, DatagridInterface $grid, ProductRepository $repository)
    {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
        ];
        $exception = new \Exception('"Pim\Bundle\DataGridBundle\Datasource\ProductDatasource" expects to be configured with "entity"');

        $this->shouldThrow($exception)->duringProcess($grid, $config);
    }

}

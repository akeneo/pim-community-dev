<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datasource;

use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ORM\PropositionRepository;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

class PropositionDatasourceSpec extends ObjectBehavior
{
    function let(ObjectManager $om, HydratorInterface $hydrator)
    {
        $this->beConstructedWith($om, $hydrator);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface');
    }

    function it_processes_a_datasource_with_product(
        $om,
        DatagridInterface $datagrid,
        PropositionRepositoryInterface $proposalRepo
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Proposition',
            'product'           => 'bar'
        ];

        $om->getRepository('Proposition')->willReturn($proposalRepo);
        $proposalRepo->createDatagridQueryBuilder()->willReturn('foo');
        $proposalRepo->applyDatagridContext('foo', 'bar')->shouldBeCalled();
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);
    }

    function it_processes_a_datasource_with_repository_configuration(
        $om,
        DatagridInterface $datagrid,
        PropositionRepositoryInterface $proposalRepo
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Proposition'
        ];

        $om->getRepository('Proposition')->willReturn($proposalRepo);
        $proposalRepo->createDatagridQueryBuilder()->willReturn('foo');
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);
    }

    function it_processes_a_datasource_with_default_query_builder(
        $om,
        DatagridInterface $datagrid,
        PropositionRepository $proposalRepo
    ) {
        $config = ['entity' => 'Proposition'];

        $om->getRepository('Proposition')->willReturn($proposalRepo);
        $proposalRepo->createQueryBuilder('p')->willReturn('foo');
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);
    }

    function it_throws_exception_when_process_with_missing_configuration(
        $om,
        DatagridInterface $datagrid
    ) {
        $this->shouldThrow(
            new \Exception(
                'Datasource is not yet built. You need to call process method before'
            )
        )
        ->duringProcess($datagrid, []);

        $config = ['repository_method' => 'createDatagridQueryBuilder'];
        $this->shouldThrow(
            new \Exception(
                sprintf(
                    '"%s" expects to be configured with "entity"',
                    'PimEnterprise\Bundle\DataGridBundle\Datasource\PropositionDatasource'
                )
            )
        )
        ->duringProcess($datagrid, $config);
    }

    function it_should_hydrates_object(
        $om,
        $hydrator,
        DatagridInterface $datagrid,
        PropositionRepositoryInterface $proposalRepo
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Proposition'
        ];

        $om->getRepository('Proposition')->willReturn($proposalRepo);
        $proposalRepo->createDatagridQueryBuilder()->willReturn('foo');
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);

        $hydrator->hydrate('foo')->willReturn('bar');

        $this->getResults()->shouldReturn('bar');
    }
}

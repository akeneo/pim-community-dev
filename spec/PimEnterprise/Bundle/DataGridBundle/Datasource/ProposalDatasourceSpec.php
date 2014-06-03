<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datasource;

use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ORM\ProposalRepository;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;

class ProposalDatasourceSpec extends ObjectBehavior
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
        ProposalRepositoryInterface $proposalRepo
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Proposal',
            'product'           => 'bar'
        ];

        $om->getRepository('Proposal')->willReturn($proposalRepo);
        $proposalRepo->createDatagridQueryBuilder()->willReturn('foo');
        $proposalRepo->applyDatagridContext('foo', 'bar')->shouldBeCalled();
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);
    }

    function it_processes_a_datasource_with_repository_configuration(
        $om,
        DatagridInterface $datagrid,
        ProposalRepositoryInterface $proposalRepo
    ) {
        $config = [
            'repository_method' => 'createDatagridQueryBuilder',
            'entity'            => 'Proposal'
        ];

        $om->getRepository('Proposal')->willReturn($proposalRepo);
        $proposalRepo->createDatagridQueryBuilder()->willReturn('foo');
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);
    }

    function it_processes_a_datasource_with_default_query_builder(
        $om,
        DatagridInterface $datagrid,
        ProposalRepository $proposalRepo
    ) {
        $config = ['entity' => 'Proposal'];

        $om->getRepository('Proposal')->willReturn($proposalRepo);
        $proposalRepo->createQueryBuilder('p')->willReturn('foo');
        $datagrid->setDatasource($this)->shouldBeCalled();

        $this->process($datagrid, $config);
    }
//     function it_should_hydrates_object($hydrator)
//     {
//         $hydrator->hydrate('foo')->willReturn('bar');

//         $this->getResults()->shouldReturn('bar');
//     }
}

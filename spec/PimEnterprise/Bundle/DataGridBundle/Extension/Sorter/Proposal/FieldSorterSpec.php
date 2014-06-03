<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Extension\Sorter\Proposal;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\DataGridBundle\Datasource\ProposalDatasource;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;

class FieldSorterSpec extends ObjectBehavior
{
    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort(
        ProposalDatasource $datasource,
        ProposalRepositoryInterface $proposalRepo
    ) {
        $datasource->getRepository()->willReturn($proposalRepo);
        $datasource->getQueryBuilder()->willReturn('qb');
        $proposalRepo->applySorter('qb', 'foo', 'ASC');

        $this->apply($datasource, 'foo', 'ASC');
    }
}

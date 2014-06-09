<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Extension\Sorter\Proposition;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\DataGridBundle\Datasource\PropositionDatasource;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

class FieldSorterSpec extends ObjectBehavior
{
    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort(
        PropositionDatasource $datasource,
        PropositionRepositoryInterface $proposalRepo
    ) {
        $datasource->getRepository()->willReturn($proposalRepo);
        $datasource->getQueryBuilder()->willReturn('qb');
        $proposalRepo->applySorter('qb', 'foo', 'ASC');

        $this->apply($datasource, 'foo', 'ASC');
    }
}

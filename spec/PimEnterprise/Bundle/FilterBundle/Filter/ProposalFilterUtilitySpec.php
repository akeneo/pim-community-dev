<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\ProposalRepositoryInterface;

class ProposalFilterUtilitySpec extends ObjectBehavior
{
    function let(ProposalRepositoryInterface $proposalRepository)
    {
        $this->beConstructedWith($proposalRepository);
    }

    function it_shoud_returns_parent_type_key_as_param_map()
    {
        $this->getParamMap()->shouldReturn(['parent_type' => 'type']);
    }

    function it_applies_a_filter_on_field(
        $proposalRepository,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);

        $proposalRepository->applyFilter($qb, 'foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyFilter($ds, 'foo', 'bar', 'baz');
    }
}

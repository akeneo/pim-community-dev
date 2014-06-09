<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

class PropositionFilterUtilitySpec extends ObjectBehavior
{
    function let(PropositionRepositoryInterface $propositionRepository)
    {
        $this->beConstructedWith($propositionRepository);
    }

    function it_shoud_returns_parent_type_key_as_param_map()
    {
        $this->getParamMap()->shouldReturn(['parent_type' => 'type']);
    }

    function it_applies_a_filter_on_field(
        $propositionRepository,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);

        $propositionRepository->applyFilter($qb, 'foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyFilter($ds, 'foo', 'bar', 'baz');
    }
}

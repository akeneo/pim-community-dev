<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;

class PropositionFilterUtilitySpec extends ObjectBehavior
{
    function let(PropositionRepositoryInterface $propositionRepository)
    {
        $this->beConstructedWith($propositionRepository);
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

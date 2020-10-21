<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Pim\Structure\Bundle\Datagrid\Attribute\FamilyFilter;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

final class FamilyFilterSpec extends ObjectBehavior
{
    public function let(FormFactoryInterface $factory, FilterUtility $util)
    {
        $this->beConstructedWith($factory, $util);
    }

    public function it_is_a_family_filter()
    {
        $this->shouldHaveType(FamilyFilter::class);
    }

    public function it_does_nothing_if_filter_value_is_empty(OrmFilterDatasourceAdapter $ds)
    {
        $this->apply($ds, null)->shouldReturn(false);
        $this->apply($ds, [])->shouldReturn(false);
        $this->apply($ds, ['value' => null])->shouldReturn(false);
        $this->apply($ds, ['value' => []])->shouldReturn(false);
    }

    //Interface FilterDatasourceAdapterInterface cannot be used here
    //because the getQueryBuilder() is not in the interface...
    public function it_does_not_fail_if_the_filter_is_applied(
        OrmFilterDatasourceAdapter $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);
        $qb->getRootAliases()->willReturn(['attribute']);
        $qb->innerJoin(Argument::cetera())->shouldBeCalled()->willReturn($qb);
        $qb->setParameter(':families', [10, 20])->shouldBeCalled();

        $this->apply($ds, ['value' => [10, 20]])->shouldReturn(true);
    }
}

<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Filter\Orm\Attribute;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\Filter\Orm\Attribute\IsSmartFilter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactoryInterface;

class IsSmartFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        FilterUtility $util,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb,
        Expr $expr
    ) {
        $this->beConstructedWith($factory, $util, 'Attribute', 'Resource');

        $ds->getQueryBuilder()->willReturn($qb);
        $qb->getRootAliases()->willReturn(['a']);
        $qb->expr()->willReturn($expr);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsSmartFilter::class);
    }

    function it_does_not_apply_the_filter_with_invalid_data($ds)
    {
        $ds->getQueryBuilder()->shouldNotBeCalled();
        $this->apply($ds, []);
        $this->apply($ds, ['value' => 'foo']);
    }

    function it_applies_a_true_filter($ds, $qb, $expr)
    {
        $expr->andX(null, null)->shouldBeCalled();
        $expr->eq("rlr.resourceId", "a.id")->shouldBeCalled();
        $expr->eq("rlr.resourceName", null)->shouldBeCalled();
        $expr->literal("Attribute")->shouldBeCalled();
        $qb
            ->leftJoin(
                'Resource',
                'rlr',
                'WITH',
                null
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->isNotNull('rlr.resourceId')->shouldBeCalled()->willReturn('rlr.resourceId IS NOT NULL');

        $qb->andWhere('rlr.resourceId IS NOT NULL')->shouldBeCalled();

        $this->apply($ds, ['value' => 1]);
    }

    function it_applies_a_false_filter($ds, $qb, $expr)
    {
        $expr->andX(null, null)->shouldBeCalled();
        $expr->eq("rlr.resourceId", "a.id")->shouldBeCalled();
        $expr->eq("rlr.resourceName", null)->shouldBeCalled();
        $expr->literal("Attribute")->shouldBeCalled();
        $qb
            ->leftJoin(
                'Resource',
                'rlr',
                'WITH',
                null
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->isNull('rlr.resourceId')->shouldBeCalled()->willReturn('rlr.resourceId IS NULL');

        $qb->andWhere('rlr.resourceId IS NULL')->shouldBeCalled();

        $this->apply($ds, ['value' => 2]);
    }
}

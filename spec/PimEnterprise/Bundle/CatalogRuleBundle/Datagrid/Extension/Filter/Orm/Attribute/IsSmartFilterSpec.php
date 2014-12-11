<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Datagrid\Extension\Filter\Orm\Attribute;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
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
        $this->shouldHaveType(
            'PimEnterprise\Bundle\CatalogRuleBundle\Datagrid\Extension\Filter\Orm\Attribute\IsSmartFilter'
        );
    }

    function it_does_not_apply_the_filter_with_invalid_data($ds)
    {
        $ds->getQueryBuilder()->shouldNotBeCalled();
        $this->apply($ds, []);
        $this->apply($ds, ['value' => 'foo']);
    }

    function it_applies_a_true_filter($ds, $qb, $expr)
    {
        $qb
            ->leftJoin(
                'Resource',
                'rlr',
                'WITH',
                'rlr.resourceId = a.id AND rlr.resourceName = :attributeClass'
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->setParameter('attributeClass', 'Attribute')->shouldBeCalled();

        $expr->isNotNull('rlr')->shouldBeCalled()->willReturn('rlr IS NOT NULL');

        $qb->andWhere('rlr IS NOT NULL')->shouldBeCalled();

        $this->apply($ds, ['value' => 1]);
    }

    function it_applies_a_false_filter($ds, $qb, $expr)
    {
        $qb
            ->leftJoin(
                'Resource',
                'rlr',
                'WITH',
                'rlr.resourceId = a.id AND rlr.resourceName = :attributeClass'
            )
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->setParameter('attributeClass', 'Attribute')->shouldBeCalled();

        $expr->isNull('rlr')->shouldBeCalled()->willReturn('rlr IS NULL');

        $qb->andWhere('rlr IS NULL')->shouldBeCalled();

        $this->apply($ds, ['value' => 2]);
    }
}

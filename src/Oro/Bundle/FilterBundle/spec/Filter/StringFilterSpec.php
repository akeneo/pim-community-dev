<?php

declare(strict_types=1);

namespace spec\Oro\Bundle\FilterBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\FilterBundle\Datasource\ExpressionBuilderInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\StringFilter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class StringFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $util)
    {
        $this->beConstructedWith($factory, $util);
        $this->init('teststring', ['data_name' => 'some_data_name']);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FilterInterface::class);
    }

    function it_is_a_string_filter()
    {
        $this->shouldHaveType(StringFilter::class);
    }

    function it_applies_empty_filter(
        FilterDatasourceAdapterInterface $ds,
        ExpressionBuilderInterface $builder
    ) {
        $ds->generateParameterName('teststring')->willReturn('teststring1234');

        $isNullExpr = 'teststring IS NULL';
        $eqExpr = 'teststring = :testrting1234';
        $orExpr = new Expr\Orx();

        $builder->isNull(Argument::type('string'))->willReturn($isNullExpr);
        $builder->eq(Argument::type('string'), Argument::type('string'), true)->willReturn($eqExpr);
        $builder->orX($isNullExpr, $eqExpr)->willReturn($orExpr);
        $ds->expr()->willReturn($builder);

        $ds->addRestriction($orExpr, 'AND', false)->shouldBeCalled();
        $ds->setParameter('teststring1234', '')->shouldBeCalled();

        $this->apply($ds, ['type' => 'empty', 'value' => ''])->shouldReturn(true);
    }
}

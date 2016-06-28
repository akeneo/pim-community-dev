<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\Batch\Model\JobExecution;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Query\Filter\Operators;
use Prophecy\Argument;

class UpdatedDateTimeFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['updated'], [Operators::SINCE_LAST_N_DAYS, Operators::SINCE_LAST_EXPORT]);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([Operators::SINCE_LAST_N_DAYS, Operators::SINCE_LAST_EXPORT]);
        $this->supportsOperator('IN')->shouldReturn(false);
        $this->supportsOperator(Operators::SINCE_LAST_N_DAYS)->shouldReturn(true);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['updated']);
    }

    function it_adds_an_updated_since_last_export_filter(
        $qb,
        JobExecution $jobExecution,
        \DateTime $endTime,
        Expr $expr,
        Comparison $comparison
    ) {
        $jobExecution->getEndTime()->willReturn($endTime);
        $endTime->format('Y-m-d H:i:s')->willReturn('2016-06-20 16:42:42');

        $qb->getRootAliases()->willReturn(['alias']);
        $qb->expr()->willReturn($expr);
        $qb->andWhere($comparison)->shouldBeCalled();

        $expr->literal('2016-06-20 16:42:42')->shouldBeCalled()->willReturn('2016-06-20 16:42:42');
        $expr->gt('alias.updated', '2016-06-20 16:42:42')->shouldBeCalled()->willReturn($comparison);

        $this->addFieldFilter(
            'updated',
            Operators::SINCE_LAST_EXPORT,
            '',
            null,
            null,
            ['lastJobExecution' => $jobExecution]
        );
    }

    function it_does_not_add_an_updated_since_last_export_filter_if_no_option_given($qb)
    {
        $qb->andWhere(Argument::cetera())->shouldNotBeCalled();

        $this->addFieldFilter(
            'updated',
            Operators::SINCE_LAST_EXPORT,
            '',
            null,
            null,
            ['lastJobExecution' => null]
        );
    }

    function it_throws_an_exception_if_last_job_execution_is_not_correctly_given()
    {
        $this
            ->shouldThrow('Pim\Component\Catalog\Exception\InvalidArgumentException')
            ->during(
                'addFieldFilter',
                [
                    'updated',
                    Operators::SINCE_LAST_EXPORT,
                    '',
                    null,
                    null,
                    ['lastJobExecution' => 'try again']
                ]
            );
    }
}

<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

class DateTimeFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $qb,
        JobInstanceRepository $jobInstanceRepository,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith(
            $jobInstanceRepository,
            $jobRepository,
            ['created', 'updated'],
            [
                '=',
                '<',
                '>',
                'BETWEEN',
                'NOT BETWEEN',
                'EMPTY',
                'NOT EMPTY',
                '!=',
                'SINCE LAST EXPORT',
                'SINCE LAST N DAYS'
            ]
        );
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_a_datetime_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateTimeFilter');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['created', 'updated']);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            '=',
            '<',
            '>',
            'BETWEEN',
            'NOT BETWEEN',
            'EMPTY',
            'NOT EMPTY',
            '!=',
            'SINCE LAST EXPORT',
            'SINCE LAST N DAYS'
        ]);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_date_fields()
    {
        $this->supportsField('created')->shouldReturn(true);
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->eq('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at = \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at = \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '=', '2014-03-15 12:03:00');
    }

    function it_adds_a_not_equal_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->neq('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at != \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at != \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '!=', '2014-03-15 12:03:00');
    }

    function it_adds_a_less_than_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->lt('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at < \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at < \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '<', '2014-03-15 12:03:00');
    }

    function it_adds_a_greater_than_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->gt('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at > \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at > \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '>', '2014-03-15 12:03:00');
    }

    function it_adds_an_empty_filter_on_an_field_in_the_query($qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNull('p.updated_at')->willReturn('p.updated_at IS NULL');

        $qb->andWhere('p.updated_at IS NULL')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_field_in_the_query($qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNotNull('p.updated_at')->shouldBeCalled()->willReturn('p.updated_at IS NOT NULL');

        $qb->andWhere('p.updated_at IS NOT NULL')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'NOT EMPTY', null);
    }

    function it_adds_a_between_filter_on_an_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->literal('2014-03-16 12:03:00')->willReturn('2014-03-16 12:03:00');

        $qb->andWhere('p.updated_at BETWEEN 2014-03-15 12:03:00 AND 2014-03-16 12:03:00')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'BETWEEN', ['2014-03-15 12:03:00', '2014-03-16 12:03:00']);
    }

    function it_adds_a_not_between_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $ltComp,
        Expr\Comparison $gtComp,
        Expr\Orx $or
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->literal('2014-03-16 12:03:00')->willReturn('2014-03-16 12:03:00');
        $expr->lt('p.updated_at', '2014-03-15 12:03:00')->willReturn($ltComp);
        $expr->gt('p.updated_at', '2014-03-16 12:03:00')->willReturn($gtComp);
        $expr->orX($ltComp, $gtComp)->willReturn($or);

        $qb->andWhere($or)->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'NOT BETWEEN', ['2014-03-15 12:03:00', '2014-03-16 12:03:00']);
    }

    function it_adds_an_updated_since_last_n_days_filter($qb, Expr $expr, Comparison $comparison)
    {
        $qb->getRootAliases()->willReturn(['alias']);
        $qb->andWhere($comparison)->shouldBeCalled();
        $qb->expr()->willReturn($expr);
        $expr->gt('alias.updated', Argument::type('string'))->shouldBeCalled()->willReturn($comparison);

        $expr->literal(Argument::type('string'))->shouldBeCalled()->willReturn('2016-06-20 16:42:42');
        $expr->gt('alias.updated', '2016-06-20 16:42:42')->shouldBeCalled()->willReturn($comparison);

        $this->addFieldFilter(
            'updated',
            'SINCE LAST N DAYS',
            30,
            null,
            null
        );
    }

    function it_adds_an_updated_since_last_export_filter(
        $qb,
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        \DateTime $startTime,
        Expr $expr,
        Comparison $comparison
    ) {
        $jobInstanceRepository->findOneBy(['code' => 'csv_product_export'])->willReturn($jobInstance);
        $jobRepository->getLastJobExecution($jobInstance, 1)->shouldBeCalled()->willReturn($jobExecution);

        $jobExecution->getStartTime()->willReturn($startTime);
        $startTime->format('Y-m-d H:i:s')->willReturn('2016-06-20 16:42:42');

        $qb->getRootAliases()->willReturn(['alias']);
        $qb->expr()->willReturn($expr);
        $qb->andWhere($comparison)->shouldBeCalled();

        $expr->literal('2016-06-20 16:42:42')->shouldBeCalled()->willReturn('2016-06-20 16:42:42');
        $expr->gt('alias.updated', '2016-06-20 16:42:42')->shouldBeCalled()->willReturn($comparison);

        $this->addFieldFilter(
            'updated',
            'SINCE LAST EXPORT',
            'csv_product_export',
            null,
            null
        );
    }

    function it_does_not_add_an_updated_since_last_export_filter_if_no_option_given(
        $qb,
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->findOneBy(['code' => 'csv_product_export'])->willReturn($jobInstance);
        $jobRepository->getLastJobExecution($jobInstance, 1)->shouldBeCalled()->willReturn(null);

        $qb->andWhere(Argument::cetera())->shouldNotBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST EXPORT',
            'csv_product_export',
            null,
            null
        );
    }

    function it_throws_an_exception_if_value_is_not_a_string_for_since_last_export()
    {
        $this
            ->shouldThrow(
                InvalidArgumentException::stringExpected('updated', 'filter', 'updated', 'integer')
            )->during(
                'addFieldFilter',
                [
                    'updated',
                    'SINCE LAST EXPORT',
                    42,
                    null,
                    null,
                ]
            );
    }

    function it_throws_an_exception_if_value_is_not_a_numeric_for_since_last_n_days()
    {
        $this
            ->shouldThrow(
                InvalidArgumentException::numericExpected('updated', 'filter', 'updated', 'string')
            )->during(
                'addFieldFilter',
                [
                    'updated',
                    'SINCE LAST N DAYS',
                    'csv_product_export',
                    null,
                    null
                ]
            );
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_a_datetime()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('updated_at', 'array with 2 elements, string or \DateTime', 'filter', 'date', print_r(123, true))
        )->during('addFieldFilter', ['updated_at', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('updated_at', 'a string with the format yyyy-mm-dd H:i:s', 'filter', 'date', 'not a valid date format')
        )->during('addFieldFilter', ['updated_at', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated_at',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                123
            )
        )->during('addFieldFilter', ['updated_at', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated_at',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addFieldFilter', ['updated_at', '>', [123, 123, 'three']]);
    }
}

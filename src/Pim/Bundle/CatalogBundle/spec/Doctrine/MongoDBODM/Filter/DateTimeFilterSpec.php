<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class DateTimeFilterSpec extends ObjectBehavior
{
    function let(
        Builder $queryBuilder,
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
                'SINCE LAST JOB',
                'SINCE LAST N DAYS'
            ]
        );
        $this->setQueryBuilder($queryBuilder);

        $queryBuilder->field(Argument::any())->willReturn($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
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
            'SINCE LAST JOB',
            'SINCE LAST N DAYS'
        ]);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->equals(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', '=', '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', '=', new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_less_than_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->lt(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', '<', '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', '<', new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_greater_than_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->gt(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', '>', '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', '>', new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->exists(false)->shouldBeCalled();

        $this->addFieldFilter('updated', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->exists(true)->shouldBeCalled();

        $this->addFieldFilter('updated', 'NOT EMPTY', null);
    }

    function it_adds_a_between_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->gte(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);
        $queryBuilder->lte(strtotime('2014-03-20 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', 'BETWEEN', ['2014-03-15 12:03:00', '2014-03-20 12:03:00']);
        $this->addFieldFilter('updated', 'BETWEEN', [new \DateTime('2014-03-15 12:03:00'), new \DateTime('2014-03-20 12:03:00')]);
    }

    function it_adds_a_not_between_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->expr()->willReturn($queryBuilder);
        $queryBuilder->addAnd($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-15 12:03:00'))->willReturn($queryBuilder)->shouldBeCalledTimes(2);
        $queryBuilder->gt(strtotime('2014-03-20 12:03:00'))->willReturn($queryBuilder)->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', 'NOT BETWEEN', ['2014-03-15 12:03:00', '2014-03-20 12:03:00']);
        $this->addFieldFilter('updated', 'NOT BETWEEN', [new \DateTime('2014-03-15 12:03:00'), new \DateTime('2014-03-20 12:03:00')]);
    }

    function it_adds_a_filter_on_products_updated_since_last_export(
        $queryBuilder,
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        \DateTime $startTime
    ) {
        $jobInstanceRepository->findOneBy(['code' => 'csv_product_export'])->willReturn($jobInstance);
        $jobRepository->getLastJobExecution($jobInstance, 1)->shouldBeCalled()->willReturn($jobExecution);

        $jobExecution->getStartTime()->willReturn($startTime);
        $startTime->setTimezone(Argument::type('\DateTimeZone'))->willReturn($startTime);
        $startTime->getTimestamp()->willReturn('1468421569');

        $queryBuilder->field('normalizedData.updated')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gt('1468421569')->shouldBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST JOB',
            'csv_product_export',
            null,
            null
        );
    }

    function it_does_not_add_an_updated_since_last_export_filter_if_no_option_given(
        $queryBuilder,
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->findOneBy(['code' => 'csv_product_export'])->willReturn($jobInstance);
        $jobRepository->getLastJobExecution($jobInstance, 1)->shouldBeCalled()->willReturn(null);

        $queryBuilder->field(Argument::any())->shouldNotBeCalled();
        $queryBuilder->gt(Argument::any())->shouldNotBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST JOB',
            'csv_product_export',
            null,
            null
        );
    }

    function it_adds_a_filter_on_products_updated_since_last_n_days($queryBuilder)
    {
        $queryBuilder->field('normalizedData.updated')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gt(Argument::any())->shouldBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST N DAYS',
            30,
            null,
            null
        );
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_datetime()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r(123, true)
            )
        )->during('addFieldFilter', ['updated', '>', 123]);
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
                    'SINCE LAST JOB',
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

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'a string with the format yyyy-mm-dd H:i:s',
                'filter',
                'date',
                'not a valid date format'
            )
        )->during('addFieldFilter', ['updated', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                123
            )
        )->during('addFieldFilter', ['updated', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addFieldFilter', ['updated', '>', [123, 123, 'three']]);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['created', 'updated']);
    }
}

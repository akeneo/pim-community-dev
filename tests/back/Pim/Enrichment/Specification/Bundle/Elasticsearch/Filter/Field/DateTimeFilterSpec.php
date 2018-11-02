<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\DateTimeFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

/**
 * DateTime filter spec for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilterSpec extends ObjectBehavior
{
    protected $userTimezone = null;

    function let(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobRepositoryInterface $jobRepository
    ) {
        $this->userTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');

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
    }

    function letGo()
    {
        date_default_timezone_set($this->userTimezone);
    }

    function it_is_initializable() {
        $this->shouldHaveType(DateTimeFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators() {
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
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_datetime_field()
    {
        $this->supportsField('created')->shouldReturn(true);
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(SearchQueryBuilder $sqb) {
        $sqb->addFilter(
            [
                'term' => [
                    'updated' => '2014-03-15T12:03:00+00:00'
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::EQUALS, '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', Operators::EQUALS, new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_filter_with_operator_lower_than(SearchQueryBuilder $sqb) {
        $sqb->addFilter(
            [
                'range' => [
                    'updated' => ['lt' => '2014-03-15T12:03:00+00:00']
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::LOWER_THAN, '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', Operators::LOWER_THAN, new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_filter_with_operator_greater_than(SearchQueryBuilder $sqb) {
        $sqb->addFilter(
            [
                'range' => [
                    'updated' => ['gt' => '2014-03-15T12:03:00+00:00']
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::GREATER_THAN, '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', Operators::GREATER_THAN, new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_filter_with_operator_between(SearchQueryBuilder $sqb) {
        $sqb->addFilter(
            [
                'range' => [
                    'updated' => [
                        'gte' => '2014-03-15T12:03:00+00:00',
                        'lte' => '2014-03-16T12:03:00+00:00'
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::BETWEEN, ['2014-03-15 12:03:00', '2014-03-16 12:03:00']);
        $this->addFieldFilter('updated', Operators::BETWEEN, [
            new \DateTime('2014-03-15 12:03:00'),
            new \DateTime('2014-03-16 12:03:00'),
        ]);
    }

    function it_adds_a_filter_with_operator_not_between(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            [
                'range' => [
                    'updated' => [
                        'gte' => '2014-03-15T12:03:00+00:00',
                        'lte' => '2014-03-16T12:03:00+00:00'
                    ]
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(['exists' => ['field' => 'updated']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::NOT_BETWEEN, ['2014-03-15 12:03:00', '2014-03-16 12:03:00']);
        $this->addFieldFilter('updated', Operators::NOT_BETWEEN, [
            new \DateTime('2014-03-15 12:03:00'),
            new \DateTime('2014-03-16 12:03:00'),
        ]);
    }

    function it_adds_a_filter_with_operator_is_empty(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(['exists' => ['field' => 'updated']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::IS_EMPTY, null);
    }

    function it_adds_a_filter_with_operator_is_not_empty(SearchQueryBuilder $sqb) {
        $sqb->addFilter(['exists' => ['field' => 'updated']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::IS_NOT_EMPTY, null);
    }

    function it_adds_a_filter_with_operator_is_not_equal(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            [
                'term' => [
                    'updated' => '2014-03-15T12:03:00+00:00'
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(['exists' => ['field' => 'updated']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::NOT_EQUAL, '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', Operators::NOT_EQUAL, new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_filter_with_operator_since_last_n_days(SearchQueryBuilder $sqb) {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $tenDaysAgo = clone $date;
        $tenDaysAgo->modify('-10 days');

        $sqb->addFilter(
            [
                'range' => [
                    'updated' => [
                        'gt' => $tenDaysAgo->format('c')
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('updated', Operators::SINCE_LAST_N_DAYS, 10);
    }

    function it_adds_a_filter_with_operator_since_last_job_with_existing_job(
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        SearchQueryBuilder $sqb
    ) {
        $timeZone = new \DateTimeZone('UTC');
        $date = new \DateTime('now',$timeZone);

        $jobExecution->getStartTime()->willReturn($date);

        $jobInstanceRepository
            ->findOneByIdentifier('csv_product_export')
            ->shouldBeCalled()
            ->willReturn($jobInstance);

        $jobRepository
            ->getLastJobExecution($jobInstance, 1)
            ->shouldBeCalled()
            ->willReturn($jobExecution);

        $sqb->addFilter(
            [
                'range' => [
                    'updated' => [
                        'gt' => $date->format('c')
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->addFieldFilter('updated', Operators::SINCE_LAST_JOB, 'csv_product_export');
    }

    function it_adds_a_filter_with_operator_since_last_job_with_not_existing_job(
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        SearchQueryBuilder $sqb
    ) {
        $timeZone = new \DateTimeZone('UTC');
        $date = new \DateTime('now',$timeZone);

        $jobExecution->getStartTime()->willReturn($date->format('c'));
        $jobInstanceRepository
            ->findOneByIdentifier('csv_product_export')
            ->shouldBeCalled()
            ->willReturn($jobInstance);

        $jobRepository
            ->getLastJobExecution($jobInstance, 1)
            ->shouldBeCalled()
            ->willReturn(null);

        $sqb->addFilter(
            [
                'range' => [
                    'updated' => [
                        'gt' => $date->format('c')
                    ]
                ]
            ]
        )->shouldNotBeCalled();

        $this->setQueryBuilder($sqb);

        $this->addFieldFilter('updated', Operators::SINCE_LAST_JOB, 'csv_product_export');
    }

    function it_throws_an_exception_with_operator_since_last_job_with_not_existing_job_instance(
        $jobInstanceRepository,
        JobExecution $jobExecution,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $timeZone = new \DateTimeZone('UTC');
        $date = new \DateTime('now',$timeZone);

        $jobExecution->getStartTime()->willReturn($date->format('c'));
        $jobInstanceRepository
            ->findOneByIdentifier('csv_product_export')
            ->shouldBeCalled()
            ->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'job_instance',
                'code',
                'The job instance does not exist',
                DateTimeFilter::class,
                'csv_product_export'
            )
        )->during('addFieldFilter', ['updated', Operators::SINCE_LAST_JOB, 'csv_product_export']);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['updated', Operators::IS_EMPTY, null]);
    }

    function it_throws_an_exception_when_the_given_value_is_a_formatable_date(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->itThrowsDateException(Operators::EQUALS, 'NOT_A_FORMATABLE_DATE');
        $this->itThrowsDateException(Operators::NOT_EQUAL, 'NOT_A_FORMATABLE_DATE');
        $this->itThrowsDateException(Operators::GREATER_THAN, 'NOT_A_FORMATABLE_DATE');
        $this->itThrowsDateException(Operators::NOT_EQUAL, 'NOT_A_FORMATABLE_DATE');
    }

    function it_throws_an_exception_when_the_given_value_is_an_array(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->itThrowsArrayExpection(Operators::BETWEEN, 'NOT_AN_ARRAY');
        $this->itThrowsArrayExpection(Operators::NOT_BETWEEN, 'NOT_AN_ARRAY');
    }


    function it_throws_an_exception_when_the_given_value_is_a_mal_formatted_array(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->itThrowsArrayStructureException(Operators::BETWEEN, ['NOT_AN_ARRAY']);
        $this->itThrowsArrayStructureException(Operators::NOT_BETWEEN, ['NOT_AN_ARRAY']);
        $this->itThrowsArrayStructureException(Operators::BETWEEN, ['NOT_AN_ARRAY_1', 'NOT_AN_ARRAY_2', 'NOT_AN_ARRAY_3']);
        $this->itThrowsArrayStructureException(Operators::NOT_BETWEEN, ['NOT_AN_ARRAY', 'NOT_AN_ARRAY_2', 'NOT_AN_ARRAY_3']);
    }

    function it_throws_an_exception_when_the_given_value_is_not_formatable_dates_array(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->itThrowsDateException(
            Operators::BETWEEN,
            ['NOT_A_FORMATABLE_DATE', '2014-03-15 12:03:00'],
            'NOT_A_FORMATABLE_DATE'
        );
        $this->itThrowsDateException(
            Operators::NOT_BETWEEN,
            ['NOT_A_FORMATABLE_DATE', '2014-03-15 12:03:00'],
            'NOT_A_FORMATABLE_DATE'
        );
        $this->itThrowsDateException(
            Operators::BETWEEN,
            ['2014-03-15 12:03:00', 'NOT_A_FORMATABLE_DATE'],
            'NOT_A_FORMATABLE_DATE'
        );
        $this->itThrowsDateException(
            Operators::NOT_BETWEEN,
            ['2014-03-15 12:03:00', 'NOT_A_FORMATABLE_DATE'],
            'NOT_A_FORMATABLE_DATE'
        );
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'created',
                DateTimeFilter::class,
                false
            )
        )->during('addFieldFilter', ['created', Operators::SINCE_LAST_JOB, false]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_numeric(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::numericExpected(
                'created',
                DateTimeFilter::class,
                false
            )
        )->during('addFieldFilter', ['created', Operators::SINCE_LAST_N_DAYS, false]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                DateTimeFilter::class
            )
        )->during('addFieldFilter', ['created', Operators::IN_CHILDREN_LIST, '2014-03-15 12:03:00']);
    }

    function itThrowsDateException($operator, $value, $propertyValueException = null) {
        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'created',
                'yyyy-mm-dd H:i:s',
                DateTimeFilter::class,
                null === $propertyValueException ? $value : $propertyValueException
            )
        )->during('addFieldFilter', ['created', $operator, $value]);
    }

    function itThrowsArrayExpection($operator, $value) {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'created',
                DateTimeFilter::class,
                $value
            )
        )->during('addFieldFilter', ['created', $operator, $value]);
    }

    function itThrowsArrayStructureException($operator, $value) {
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'created',
                sprintf('should contain 2 strings with the format "%s"', 'yyyy-mm-dd H:i:s'),
                DateTimeFilter::class,
                $value
            )
        )->during('addFieldFilter', ['created', $operator, $value]);
    }
}

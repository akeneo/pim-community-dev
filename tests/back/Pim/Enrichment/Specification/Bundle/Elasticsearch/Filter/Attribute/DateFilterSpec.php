<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\DateFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;

/**
 * Date filter spec for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilterSpec extends ObjectBehavior
{
    protected $timezone;

    function let(
        ElasticsearchFilterValidator $filterValidator
    ) {
        $this->timezone = ini_get('date.timezone');
        ini_set('date.timezone', 'UTC');

        $this->beConstructedWith(
            $filterValidator,
            ['pim_catalog_date'],
            [
                '=',
                '<',
                '>',
                'BETWEEN',
                'NOT BETWEEN',
                'EMPTY',
                'NOT EMPTY',
                '!=',
            ]
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(DateFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
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
            '!='
        ]);
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_date_attribute(AttributeInterface $publishedOn, AttributeInterface $size)
    {
        $publishedOn->getType()->willReturn('pim_catalog_date');
        $size->getType()->willReturn('pim_catalog_number');

        $this->supportsAttribute($publishedOn)->shouldReturn(true);
        $this->supportsAttribute($size)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'term' => [
                    'values.publishedOn-date.ecommerce.en_US' => '2014-03-15'
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::EQUALS,
            '2014-03-15',
            'en_US',
            'ecommerce',
            []
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::EQUALS,
            new \DateTime('2014-03-15'),
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_lower_than(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.publishedOn-date.ecommerce.en_US' => ['lt' => '2014-03-15']
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::LOWER_THAN,
            '2014-03-15',
            'en_US',
            'ecommerce',
            []
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::LOWER_THAN,
            new \DateTime('2014-03-15'),
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.publishedOn-date.ecommerce.en_US' => ['gt' => '2014-03-15']
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::GREATER_THAN,
            '2014-03-15',
            'en_US',
            'ecommerce',
            []
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::GREATER_THAN,
            new \DateTime('2014-03-15'),
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_between(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'values.publishedOn-date.ecommerce.en_US' => [
                        'gte' => '2014-03-15',
                        'lte' => '2014-03-16'
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::BETWEEN,
            ['2014-03-15', '2014-03-16'],
            'en_US',
            'ecommerce',
            []
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::BETWEEN,
            [new \DateTime('2014-03-15'), new \DateTime('2014-03-16')],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_not_between(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'range' => [
                    'values.publishedOn-date.ecommerce.en_US' => [
                        'gte' => '2014-03-15',
                        'lte' => '2014-03-16'
                    ]
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_BETWEEN,
            ['2014-03-15', '2014-03-16'],
            'en_US',
            'ecommerce',
            []
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_BETWEEN,
            [new \DateTime('2014-03-15'), new \DateTime('2014-03-16')],
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_empty(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']])->shouldBeCalled();
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['terms' => ['attributes_for_this_level' => ['publishedOn']]],
                        ['terms' => ['attributes_of_ancestors' => ['publishedOn']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::IS_EMPTY,
            null,
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::IS_NOT_EMPTY,
            null,
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_operator_is_not_equal(
        ElasticsearchFilterValidator $filterValidator,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('publishedOn', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('publishedOn', 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'term' => [
                    'values.publishedOn-date.ecommerce.en_US' => '2014-03-15'
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_EQUAL,
            '2014-03-15',
            'en_US',
            'ecommerce',
            []
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_EQUAL,
            new \DateTime('2014-03-15'),
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_with_a_relative_date_in_the_past(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $sqb,
        AttributeInterface $releaseDate
    ) {
        $releaseDate->getCode()->willReturn('release_date');
        $releaseDate->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('release_date', null)->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('release_date', null)->shouldBeCalled();

        $relativeDate = new \DateTimeImmutable('-1 week', new \DateTimeZone('UTC'));

        $sqb->addFilter(
            [
                'range' => [
                    'values.release_date-date.<all_channels>.<all_locales>' => [
                        'gt' => $relativeDate->format('Y-m-d'),
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($releaseDate, '>', '-1 week');
    }

    function it_adds_a_filter_with_a_relative_date_in_the_future(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $sqb,
        AttributeInterface $releaseDate
    ) {
        $releaseDate->getCode()->willReturn('release_date');
        $releaseDate->getBackendType()->willReturn('date');

        $filterValidator->validateLocaleForAttribute('release_date', 'en_US')->shouldBeCalled();
        $filterValidator->validateChannelForAttribute('release_date', null)->shouldBeCalled();

        $relativeDate = new \DateTimeImmutable('+6 months', new \DateTimeZone('UTC'));

        $sqb->addFilter(
            [
                'term' => [
                    'values.release_date-date.<all_channels>.en_US' => $relativeDate->format('Y-m-d'),
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($releaseDate, '=', '+6 months', 'en_US');
    }

    function it_throws_an_exception_for_relative_dates_on_unsupported_operators(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $sqb,
        AttributeInterface $releaseDate
    ) {
        $releaseDate->getCode()->willReturn('release_date');
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'release_date',
                'yyyy-mm-dd',
                DateFilter::class,
                '-1 week'
            )
        )->during('addAttributeFilter', [$releaseDate, Operators::BETWEEN, ['-1 week', 'now']]);

        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'release_date',
                'yyyy-mm-dd',
                DateFilter::class,
                'now'
            )
        )->during('addAttributeFilter', [$releaseDate, Operators::NOT_BETWEEN, ['2018-01-01', 'now']]);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $publishedOn)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$publishedOn, Operators::IS_EMPTY, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_a_formatable_date(
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $this->setQueryBuilder($sqb);

        $this->itThrowsDateException($publishedOn, Operators::EQUALS, 'NOT_A_FORMATABLE_DATE');
        $this->itThrowsDateException($publishedOn, Operators::NOT_EQUAL, 'NOT_A_FORMATABLE_DATE');
        $this->itThrowsDateException($publishedOn, Operators::GREATER_THAN, 'NOT_A_FORMATABLE_DATE');
        $this->itThrowsDateException($publishedOn, Operators::NOT_EQUAL, 'NOT_A_FORMATABLE_DATE');
    }

    function it_throws_an_exception_when_the_given_value_is_an_array(
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $this->setQueryBuilder($sqb);

        $this->itThrowsArrayExpection($publishedOn, Operators::BETWEEN, 'NOT_AN_ARRAY');
        $this->itThrowsArrayExpection($publishedOn, Operators::NOT_BETWEEN, 'NOT_AN_ARRAY');
    }


    function it_throws_an_exception_when_the_given_value_is_a_mal_formatted_array(
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $this->setQueryBuilder($sqb);

        $this->itThrowsArrayStructureException($publishedOn, Operators::BETWEEN, ['NOT_AN_ARRAY']);
        $this->itThrowsArrayStructureException($publishedOn, Operators::NOT_BETWEEN, ['NOT_AN_ARRAY']);
        $this->itThrowsArrayStructureException($publishedOn, Operators::BETWEEN, ['NOT_AN_ARRAY_1', 'NOT_AN_ARRAY_2', 'NOT_AN_ARRAY_3']);
        $this->itThrowsArrayStructureException($publishedOn, Operators::NOT_BETWEEN, ['NOT_AN_ARRAY', 'NOT_AN_ARRAY_2', 'NOT_AN_ARRAY_3']);
    }

    function it_throws_an_exception_when_the_given_value_is_not_formatable_dates_array(
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $this->setQueryBuilder($sqb);

        $this->itThrowsDateException(
            $publishedOn,
            Operators::BETWEEN,
            ['NOT_A_FORMATABLE_DATE', '2014-03-15'],
            'NOT_A_FORMATABLE_DATE'
        );
        $this->itThrowsDateException(
            $publishedOn,
            Operators::NOT_BETWEEN,
            ['NOT_A_FORMATABLE_DATE', '2014-03-15'],
            'NOT_A_FORMATABLE_DATE'
        );
        $this->itThrowsDateException(
            $publishedOn,
            Operators::BETWEEN,
            ['2014-03-15', 'NOT_A_FORMATABLE_DATE'],
            'NOT_A_FORMATABLE_DATE'
        );
        $this->itThrowsDateException(
            $publishedOn,
            Operators::NOT_BETWEEN,
            ['2014-03-15', 'NOT_A_FORMATABLE_DATE'],
            'NOT_A_FORMATABLE_DATE'
        );
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                DateFilter::class
            )
        )->during('addAttributeFilter', [$publishedOn, Operators::IN_CHILDREN_LIST, '2014-03-15']);
    }

    function itThrowsDateException(AttributeInterface $attribute, $operator, $value, $propertyValueException = null) {
        $this->shouldThrow(
            InvalidPropertyException::dateExpected(
                'publishedOn',
                'yyyy-mm-dd',
                DateFilter::class,
                null === $propertyValueException ? $value : $propertyValueException
            )
        )->during('addAttributeFilter', [$attribute, $operator, $value]);
    }

    function itThrowsArrayExpection(AttributeInterface $attribute, $operator, $value) {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'publishedOn',
                DateFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, $operator, $value]);
    }

    function itThrowsArrayStructureException(AttributeInterface $attribute, $operator, $value) {
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'publishedOn',
                sprintf('should contain 2 strings with the format "%s"', 'yyyy-mm-dd'),
                DateFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, $operator, $value]);
    }

    function letGo()
    {
        ini_set('date.timezone', $this->timezone);
    }
}

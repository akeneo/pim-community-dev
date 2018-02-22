<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Filter\Attribute;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Filter\Attribute\DateFilter;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Filter\Attribute\ProposalAttributePathResolver;

class DateFilterSpec extends ObjectBehavior
{
    private $timezone;

    function let(ProposalAttributePathResolver $attributePathResolver)
    {
        $this->timezone = ini_get('date.timezone');
        ini_set('date.timezone', 'UTC');

        $this->beConstructedWith(
            $attributePathResolver,
            ['pim_catalog_date'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
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
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.publishedOn-date.ecommerce.en_US' => '2014-03-15']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::EQUALS,
            '2014-03-15'
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::EQUALS,
            new \DateTime('2014-03-15')
        );
    }

    function it_adds_a_filter_with_operator_lower_than(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.publishedOn-date.ecommerce.en_US' => ['lt' => '2014-03-15']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::LOWER_THAN,
            '2014-03-15'
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::LOWER_THAN,
            new \DateTime('2014-03-15')
        );
    }

    function it_adds_a_filter_with_operator_greater_than(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.publishedOn-date.ecommerce.en_US' => ['gt' => '2014-03-15']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::GREATER_THAN,
            '2014-03-15'
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::GREATER_THAN,
            new \DateTime('2014-03-15')
        );
    }

    function it_adds_a_filter_with_operator_between(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.publishedOn-date.ecommerce.en_US' => ['gte' => '2014-03-15', 'lte' => '2014-03-16']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::BETWEEN,
            ['2014-03-15', '2014-03-16']
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::BETWEEN,
            [new \DateTime('2014-03-15'), new \DateTime('2014-03-16')]
        );
    }

    function it_adds_a_filter_with_operator_not_between(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['values.publishedOn-date.ecommerce.en_US' => ['gte' => '2014-03-15', 'lte' => '2014-03-16']]]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_BETWEEN,
            ['2014-03-15', '2014-03-16']
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_BETWEEN,
            [new \DateTime('2014-03-15'), new \DateTime('2014-03-16')]
        );
    }

    function it_adds_a_filter_with_operator_is_empty(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::IS_EMPTY,
            null
        );
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::IS_NOT_EMPTY,
            null
        );
    }

    function it_adds_a_filter_with_operator_is_not_equal(
        $attributePathResolver,
        AttributeInterface $publishedOn,
        SearchQueryBuilder $sqb
    ) {
        $publishedOn->getCode()->willReturn('publishedOn');
        $publishedOn->getBackendType()->willReturn('date');

        $attributePathResolver->getAttributePaths($publishedOn)->willReturn(['values.publishedOn-date.ecommerce.en_US']);

        $sqb->addMustNot(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['values.publishedOn-date.ecommerce.en_US' => '2014-03-15']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['exists' => ['field' => 'values.publishedOn-date.ecommerce.en_US']]
                    ],
                    'minimum_should_match' => 1
                ]
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_EQUAL,
            '2014-03-15'
        );
        $this->addAttributeFilter(
            $publishedOn,
            Operators::NOT_EQUAL,
            new \DateTime('2014-03-15')
        );
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

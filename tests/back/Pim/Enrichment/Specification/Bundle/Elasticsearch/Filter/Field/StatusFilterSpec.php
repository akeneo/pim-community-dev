<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\StatusFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

/**
 * Enabled filter spec for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StatusFilterSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith(
            ['enabled'],
            ['=', '!=']
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(StatusFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators() {
        $this->getOperators()->shouldReturn(['=', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_enabled_field()
    {
        $this->supportsField('enabled')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(SearchQueryBuilder $sqb) {
        $sqb->addFilter([
            'term' => [
                'enabled' => false
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('enabled', Operators::EQUALS, false, null, null, []);
    }

    function it_adds_a_filter_with_operator_not_equal(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            [
                'term' => [
                    'enabled' => false,
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'enabled',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('enabled', Operators::NOT_EQUAL, false, null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['enabled', Operators::NOT_EQUAL, false, null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_boolean_with_equals(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::booleanExpected(
                'enabled',
                StatusFilter::class,
                'NOT_A_BOOLEAN'
            )
        )->during('addFieldFilter', ['enabled', Operators::EQUALS, 'NOT_A_BOOLEAN', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_boolean_with_not_equal(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::booleanExpected(
                'enabled',
                StatusFilter::class,
                'NOT_A_BOOLEAN'
            )
        )->during('addFieldFilter', ['enabled', Operators::NOT_EQUAL, 'NOT_A_BOOLEAN', null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                StatusFilter::class
            )
        )->during('addFieldFilter', ['enabled', Operators::IN_CHILDREN_LIST, false, null, null, []]);
    }
}

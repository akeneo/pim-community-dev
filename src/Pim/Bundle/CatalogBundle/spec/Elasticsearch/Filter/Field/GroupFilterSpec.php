<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\GroupFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Group filter spec for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['groups', 'variant_group'],
            ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'IN',
                'NOT IN',
                'EMPTY',
                'NOT EMPTY',
            ]
        );
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_groups_field()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('variant_group')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'terms' => [
                    'groups' => ['groupsA'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('groups', Operators::IN_LIST, ['groupsA'], null, null, []);
    }

    function it_adds_a_filter_with_operator_not_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'terms' => [
                    'groups' => ['groupsA'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('groups', Operators::NOT_IN_LIST, ['groupsA'], null, null, []);
    }

    function it_adds_a_filter_with_operator_is_empty(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'exists' => ['field' => 'groups'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('groups', Operators::IS_EMPTY, ['groupsA'], null, null, []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'exists' => ['field' => 'groups'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('groups', Operators::IS_NOT_EMPTY, ['groupsA'], null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['groups', Operators::IN_LIST, ['groupsA'], null, null, []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['groups', Operators::IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['groups', Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_is_empty(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['groups', Operators::IS_EMPTY, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_is_not_empty(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'groups',
                GroupFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['groups', Operators::IS_NOT_EMPTY, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'groups',
                GroupFilter::class,
                false
            )
        )->during('addFieldFilter', ['groups', Operators::IN_LIST, [false], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                GroupFilter::class
            )
        )->during('addFieldFilter', ['groups', Operators::IN_CHILDREN_LIST, ['groupsA'], null, null, []]);
    }
}

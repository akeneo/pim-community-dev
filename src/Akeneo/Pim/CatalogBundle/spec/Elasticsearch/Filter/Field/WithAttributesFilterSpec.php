<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\WithAttributesFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WithAttributesFilterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith(
            $attributeRepository,
            ['attributes', 'attributes_for_this_level'],
            ['IN', 'NOT IN']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WithAttributesFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN']);
        $this->supportsOperator('EMPTY')->shouldReturn(false);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_attributes_fields()
    {
        $this->supportsField('attributes')->shouldReturn(true);
        $this->supportsField('attributes_for_this_level')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(
        $attributeRepository,
        SearchQueryBuilder $sqb,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attributeA')->willReturn($attribute);

        $sqb->addFilter(
            [
                'terms' => [
                    'attributes' => ['attributeA'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('attributes', Operators::IN_LIST, ['attributeA'], null, null, []);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $attributeRepository,
        SearchQueryBuilder $sqb,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attributeA')->willReturn($attribute);

        $sqb->addMustNot(
            [
                'terms' => [
                    'attributes' => ['attributeA'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('attributes', Operators::NOT_IN_LIST, ['attributeA'], null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['attributes', Operators::IN_LIST, ['foo'], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributes',
                WithAttributesFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['attributes', Operators::IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributes',
                WithAttributesFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['attributes', Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'attributes',
                WithAttributesFilter::class,
                false
            )
        )->during('addFieldFilter', ['attributes', Operators::IN_LIST, [false], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_known_family(
        $attributeRepository,
        SearchQueryBuilder $sqb
    ) {
        $attributeRepository->findOneByIdentifier('UNKNOWN_FAMILY')->willReturn(null);
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            new ObjectNotFoundException('Object "attribute" with code "UNKNOWN_FAMILY" does not exist')
        )->during('addFieldFilter', ['attributes', Operators::IN_LIST, ['UNKNOWN_FAMILY'], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeRepository,
        SearchQueryBuilder $sqb,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attributeA')->willReturn($attribute);
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                WithAttributesFilter::class
            )
        )->during('addFieldFilter', ['attributes', Operators::IN_CHILDREN_LIST, ['attributeA'], null, null, []]);
    }
}

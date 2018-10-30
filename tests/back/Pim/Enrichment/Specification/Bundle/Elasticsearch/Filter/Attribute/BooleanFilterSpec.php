<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\BooleanFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            ['pim_catalog_boolean'],
            ['=', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BooleanFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            '=',
            '!=',
        ]);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributeValidatorHelper->validateLocale($booleanAttribute, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($booleanAttribute, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'term' => [
                    'values.boolean-boolean.ecommerce.en_US' => true,
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($booleanAttribute, Operators::EQUALS, true, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributeValidatorHelper,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributeValidatorHelper->validateLocale($booleanAttribute, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($booleanAttribute, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot([
                'term' => [
                    'values.boolean-boolean.ecommerce.en_US' => false,
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter([
                'exists' => ['field' => 'values.boolean-boolean.ecommerce.en_US'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($booleanAttribute, Operators::NOT_EQUAL, false, 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(
        AttributeInterface $booleanAttribute
    ) {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$booleanAttribute, Operators::EQUALS, false, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_boolean(
        $attributeValidatorHelper,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributeValidatorHelper->validateLocale($booleanAttribute, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($booleanAttribute, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::booleanExpected(
                'boolean',
                BooleanFilter::class,
                123
            )
        )->during('addAttributeFilter', [$booleanAttribute, Operators::EQUALS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');

        $attributeValidatorHelper->validateLocale($booleanAttribute, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($booleanAttribute, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                BooleanFilter::class
            )
        )->during('addAttributeFilter', [
            $booleanAttribute,
            Operators::IN_CHILDREN_LIST,
            true,
            'en_US',
            'ecommerce', []
        ]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');
        $booleanAttribute->isLocaleSpecific()->willReturn(true);
        $booleanAttribute->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "name" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($booleanAttribute, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'boolean',
                BooleanFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$booleanAttribute, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $booleanAttribute,
        SearchQueryBuilder $sqb
    ) {
        $booleanAttribute->getCode()->willReturn('boolean');
        $booleanAttribute->getBackendType()->willReturn('boolean');
        $booleanAttribute->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "name" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($booleanAttribute, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'boolean',
                BooleanFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$booleanAttribute, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }
}

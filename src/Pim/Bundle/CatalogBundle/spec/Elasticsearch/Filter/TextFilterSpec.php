<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

class TextFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            ['pim_catalog_text'],
            ['STARTS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'IN', 'EMPTY', 'NOT EMPTY', '!=']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\TextFilter');
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('\Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
        $this->shouldBeAnInstanceOf('\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\AbstractFilter');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            'IN',
            'EMPTY',
            'NOT EMPTY',
            '!=',
        ]);
        $this->supportsOperator('STARTS WITH')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'term' => [
                    'values.name-varchar.en_US.ecommerce' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::EQUALS, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_equal(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot([
                'term' => [
                    'values.name-varchar.en_US.ecommerce' => 'Sony',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter([
                'exists' => ['field' => 'values.name-varchar.en_US.ecommerce'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::NOT_EQUAL, 'Sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_empty(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot([
                'exists' => [
                    'field' => 'values.name-varchar.en_US.ecommerce',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'exists' => [
                    'field' => 'values.name-varchar.en_US.ecommerce',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_contains(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'query_string' => [
                    'default_field' => 'values.name-varchar.en_US.ecommerce',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::CONTAINS, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_does_not_contain(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'values.name-varchar.en_US.ecommerce',
                    'query'         => '*sony*',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter([
            'exists' => [
                'field' => 'values.name-varchar.en_US.ecommerce'
            ],
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::DOES_NOT_CONTAIN, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_starts_with(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter([
                'query_string' => [
                    'default_field' => 'values.name-varchar.en_US.ecommerce',
                    'query'         => 'sony*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($name, Operators::STARTS_WITH, 'sony', 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $name)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'name',
                'Pim\Bundle\CatalogBundle\Elasticsearch\Filter\TextFilter',
                123
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');

        $attributeValidatorHelper->validateLocale($name, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($name, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                'Pim\Bundle\CatalogBundle\Elasticsearch\Filter\TextFilter'
            )
        )->during('addAttributeFilter', [$name, Operators::IN_CHILDREN_LIST, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');
        $name->isLocaleSpecific()->willReturn(true);
        $name->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "name" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($name, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'name',
                'Pim\Bundle\CatalogBundle\Elasticsearch\Filter\TextFilter',
                $e
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $name,
        SearchQueryBuilder $sqb
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');
        $name->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "name" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($name, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'name',
                'Pim\Bundle\CatalogBundle\Elasticsearch\Filter\TextFilter',
                $e
            )
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);
    }
}

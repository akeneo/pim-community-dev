<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\OptionFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class OptionFilterSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attributeValidatorHelper,
        AttributeOptionRepository $attributeOptionRepository
    ) {
        $operators = ['IN', 'EMPTY', 'NOT_EMPTY', 'NOT IN'];
        $this->beConstructedWith(
            $attributeValidatorHelper,
            $attributeOptionRepository,
            ['pim_catalog_simpleselect', 'pim_catalog_multiselect'],
            $operators
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'IN',
                'EMPTY',
                'NOT_EMPTY',
                'NOT IN',
            ]
        );
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_attribute_option(
        AttributeInterface $color,
        AttributeInterface $brands,
        AttributeInterface $description
    ) {
        $color->getType()->willReturn('pim_catalog_simpleselect');
        $brands->getType()->willReturn('pim_catalog_multiselect');
        $description->getType()->willReturn('pim_catalog_text');


        $this->supportsAttribute($color)->shouldReturn(true);
        $this->supportsAttribute($brands)->shouldReturn(true);
        $this->supportsAttribute($description)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_empty(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => "values.color-option.ecommerce.en_US",
                ],
            ]
        )->shouldBeCalled();
        $sqb->addFilter(['exists' => ['field' => 'family.code']])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_EMPTY, ['black'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_in_list(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([['code' => 'black']]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'terms' => [
                    'values.color-option.ecommerce.en_US' => ['black'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IN_LIST, ['black'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => "values.color-option.ecommerce.en_US",
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::IS_NOT_EMPTY, ['black'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([['code' => 'black']]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'terms' => [
                    'values.color-option.ecommerce.en_US' => ['black'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $color)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'color',
                OptionFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'color',
                OptionFilter::class,
                true
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, [true], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_search_values_does_not_exists(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            new ObjectNotFoundException(
                sprintf(
                    'Object "%s" with code "%s" does not exist',
                    $color->getWrappedObject()->getBackendType(),
                    'black'
                )
            )
        )->during('addAttributeFilter', [$color, Operators::IN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');

        $attributeOptionRepository->findCodesByIdentifiers('color', ['black'])->willReturn([['code' => 'black']]);

        $attributeValidatorHelper->validateLocale($color, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($color, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                Operators::IN_CHILDREN_LIST,
                OptionFilter::class
            )
        )->during('addAttributeFilter', [$color, Operators::IN_CHILDREN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');
        $color->isLocaleSpecific()->willReturn(true);
        $color->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "color" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($color, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'color',
                OptionFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $color,
        SearchQueryBuilder $sqb
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');
        $color->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "color" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($color, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'color',
                OptionFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$color, Operators::NOT_IN_LIST, ['black'], 'en_US', 'ecommerce', []]);
    }
}

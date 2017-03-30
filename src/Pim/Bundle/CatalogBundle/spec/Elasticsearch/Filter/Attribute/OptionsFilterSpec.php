<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute\OptionsFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Options filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilterSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attributeValidatorHelper,
        AttributeOptionRepository $attributeOptionRepository
    ) {
        $this->beConstructedWith(
            $attributeValidatorHelper,
            $attributeOptionRepository,
            ['pim_catalog_multiselect'],
            ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractAttributeFilter::class);
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

    function it_supports_multiselect_attribute_field(AttributeInterface $tags, AttributeInterface $price)
    {
        $tags->getType()->willReturn('pim_catalog_multiselect');
        $price->getType()->willReturn('pim_catalog_price');

        $this->supportsAttribute($tags)->shouldReturn(true);
        $this->supportsAttribute($price)->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeOptionRepository
            ->findByIdentifiers('tags', ['summer'])
            ->willReturn([['code' => 'summer'], ['code' => 'winter']]);

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'terms' => [
                    'values.tags-options.ecommerce.en_US' => ['summer'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($tags, Operators::IN_LIST, ['summer'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeOptionRepository
            ->findByIdentifiers('tags', ['summer'])
            ->willReturn([['code' => 'summer'], ['code' => 'winter']]);

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'terms' => [
                    'values.tags-options.ecommerce.en_US' => ['summer'],
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => ['field' => 'values.tags-options.ecommerce.en_US'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($tags, Operators::NOT_IN_LIST, ['summer'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_empty(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $sqb->addMustNot(
            [
                'exists' => ['field' => 'values.tags-options.ecommerce.en_US'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($tags, Operators::IS_EMPTY, ['summer'], 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => ['field' => 'values.tags-options.ecommerce.en_US'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($tags, Operators::IS_NOT_EMPTY, ['summer'], 'en_US', 'ecommerce', []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $tags)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$tags, Operators::IN_LIST, ['summer'], 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'tags',
                OptionsFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$tags, Operators::IN_LIST, 'NOT_AN_ARRAY', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_list(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'tags',
                OptionsFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$tags, Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', 'en_US', 'ecommerce', []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_is_empty(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'tags',
                OptionsFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$tags, Operators::IS_EMPTY, 'NOT_AN_ARRAY', 'en_US', 'ecommerce', []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_is_not_empty(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'tags',
                OptionsFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addAttributeFilter', [$tags, Operators::IS_NOT_EMPTY, 'NOT_AN_ARRAY', 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'tags',
                OptionsFilter::class,
                false
            )
        )->during('addAttributeFilter', [$tags, Operators::IN_LIST, [false], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeOptionRepository
            ->findByIdentifiers('tags', ['summer'])
            ->willReturn([['code' => 'summer'], ['code' => 'winter']]);

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                OptionsFilter::class
            )
        )->during('addAttributeFilter', [$tags, Operators::IN_CHILDREN_LIST, ['summer'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');
        $tags->isLocaleSpecific()->willReturn(true);
        $tags->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "tags" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($tags, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'tags',
                OptionsFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$tags, Operators::IN_LIST, ['summer'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');
        $tags->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "tags" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($tags, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'tags',
                OptionsFilter::class,
                $e
            )
        )->during('addAttributeFilter', [$tags, Operators::IN_LIST, ['summer'], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_execption_when_it_is_a_not_existing_option(
        $attributeValidatorHelper,
        $attributeOptionRepository,
        AttributeInterface $tags,
        SearchQueryBuilder $sqb
    ) {
        $tags->getCode()->willReturn('tags');
        $tags->getBackendType()->willReturn('options');

        $attributeOptionRepository->findByIdentifiers('tags', ['spring'])->willReturn([]);

        $attributeValidatorHelper->validateLocale($tags, 'en_US')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($tags, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            new ObjectNotFoundException(
                sprintf('Object "%s" with code "%s" does not exist', 'options', 'spring')
            )
        )->during('addAttributeFilter', [$tags, Operators::IN_CHILDREN_LIST, ['spring'], 'en_US', 'ecommerce', []]);
    }
}

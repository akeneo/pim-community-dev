<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute\BaseAttributeSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class BaseAttributeSorterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith($attributeValidatorHelper, ['pim_catalog_custom_attribute']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BaseAttributeSorter::class);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement(AttributeSorterInterface::class);
    }

    function it_adds_a_sorter_with_operator_ascendant_no_locale_and_no_scope(
        $attributeValidatorHelper,
        AttributeInterface $customAttribute,
        SearchQueryBuilder $sqb
    ) {
        $customAttribute->getCode()->willReturn('a_custom_attribute');
        $customAttribute->getBackendType()->willReturn('backend_type');
        $sqb->addSort([
            'values.a_custom_attribute-backend_type.<all_channels>.<all_locales>' => [
                'order' => 'ASC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();


        $attributeValidatorHelper->validateLocale($customAttribute, null)->shouldBeCalled();
        $attributeValidatorHelper->validateScope($customAttribute, null)->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($customAttribute, DIRECTIONS::ASCENDING, null, null);
    }

    function it_adds_a_sorter_with_operator_ascendant_locale_and_scope(
        $attributeValidatorHelper,
        AttributeInterface $customAttribute,
        SearchQueryBuilder $sqb
    ) {
        $customAttribute->getCode()->willReturn('a_custom_attribute');
        $customAttribute->getBackendType()->willReturn('backend_type');

        $sqb->addSort([
            'values.a_custom_attribute-backend_type.ecommerce.fr_FR' => [
                'order' => 'ASC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $attributeValidatorHelper->validateLocale($customAttribute, 'fr_FR')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($customAttribute, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($customAttribute, DIRECTIONS::ASCENDING, 'fr_FR', 'ecommerce');
    }

    function it_adds_a_sorter_with_operator_descendant_locale_and_scope(
        $attributeValidatorHelper,
        AttributeInterface $customAttribute,
        SearchQueryBuilder $sqb
    ) {
        $customAttribute->getCode()->willReturn('a_custom_attribute');
        $customAttribute->getBackendType()->willReturn('backend_type');

        $sqb->addSort([
            'values.a_custom_attribute-backend_type.ecommerce.fr_FR' => [
                'order' => 'DESC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $attributeValidatorHelper->validateLocale($customAttribute, 'fr_FR')->shouldBeCalled();
        $attributeValidatorHelper->validateScope($customAttribute, 'ecommerce')->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($customAttribute, DIRECTIONS::DESCENDING, 'fr_FR', 'ecommerce');
    }

    function it_supports_only_custom_attributes(
        AttributeInterface $customAttribute,
        AttributeInterface $aPrice
    ) {
        $customAttribute->getType()->willReturn('pim_catalog_custom_attribute');
        $aPrice->getType()->willReturn('pim_catalog_price');

        $this->supportsAttribute($customAttribute)->shouldReturn(true);
        $this->supportsAttribute($aPrice)->shouldReturn(false);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(
        AttributeInterface $customAttribute
    ) {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addAttributeSorter', [$customAttribute, Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(
        AttributeInterface $customAttribute,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                BaseAttributeSorter::class
            )
        )->during('addAttributeSorter', [$customAttribute, 'A_BAD_DIRECTION']);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $customAttribute,
        SearchQueryBuilder $sqb
    ) {
        $customAttribute->getCode()->willReturn('custom_code');
        $customAttribute->getBackendType()->willReturn('customAttribute');
        $customAttribute->isLocaleSpecific()->willReturn(true);
        $customAttribute->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "custom_code" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($customAttribute, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'custom_code',
                BaseAttributeSorter::class,
                $e
            )
        )->during(
            'addAttributeSorter',
            [$customAttribute, Directions::ASCENDING, 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $customAttribute,
        SearchQueryBuilder $sqb
    ) {
        $customAttribute->getCode()->willReturn('custom_code');
        $customAttribute->getBackendType()->willReturn('customAttribute');
        $customAttribute->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "custom_code" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($customAttribute, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'custom_code',
                BaseAttributeSorter::class,
                $e
            )
        )->during(
            'addAttributeSorter',
            [$customAttribute, Directions::DESCENDING, 'en_US', 'ecommerce', []]
        );
    }
}

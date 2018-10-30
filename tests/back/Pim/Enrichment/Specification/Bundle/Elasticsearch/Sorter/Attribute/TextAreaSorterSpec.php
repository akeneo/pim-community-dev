<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute\TextAreaSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;

class TextAreaSorterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith($attributeValidatorHelper, ['pim_catalog_textarea']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextAreaSorter::class);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement(AttributeSorterInterface::class);
    }

    function it_adds_a_sorter_with_operator_ascendant_no_locale_and_no_scope(
        AttributeInterface $aTextArea,
        SearchQueryBuilder $sqb
    ) {
        $aTextArea->getCode()->willReturn('a_text_area');
        $aTextArea->getBackendType()->willReturn('textarea');
        $sqb->addSort([
            'values.a_text_area-textarea.<all_channels>.<all_locales>.preprocessed' => [
                'order' => 'ASC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($aTextArea, DIRECTIONS::ASCENDING, null, null);
    }

    function it_adds_a_sorter_with_operator_ascendant_locale_and_scope(
        AttributeInterface $aTextArea,
        SearchQueryBuilder $sqb
    ) {
        $aTextArea->getCode()->willReturn('a_text_area');
        $aTextArea->getBackendType()->willReturn('textarea');

        $sqb->addSort([
            'values.a_text_area-textarea.ecommerce.fr_FR.preprocessed' => [
                'order' => 'ASC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($aTextArea, DIRECTIONS::ASCENDING, 'fr_FR', 'ecommerce');
    }

    function it_adds_a_sorter_with_operator_descendant_locale_and_scope(
        AttributeInterface $aTextArea,
        SearchQueryBuilder $sqb
    ) {
        $aTextArea->getCode()->willReturn('a_text_area');
        $aTextArea->getBackendType()->willReturn('textarea');

        $sqb->addSort([
            'values.a_text_area-textarea.ecommerce.fr_FR.preprocessed' => [
                'order' => 'DESC',
                'missing' => '_last',
                'unmapped_type' => 'long'
            ]
        ])->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeSorter($aTextArea, DIRECTIONS::DESCENDING, 'fr_FR', 'ecommerce');
    }

    function it_supports_only_text_area_attribute(
        AttributeInterface $aTextArea,
        AttributeInterface $aPrice
    ) {
        $aTextArea->getType()->willReturn('pim_catalog_textarea');
        $aPrice->getType()->willReturn('pim_catalog_price');

        $this->supportsAttribute($aTextArea)->shouldReturn(true);
        $this->supportsAttribute($aPrice)->shouldReturn(false);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(
        AttributeInterface $aTextArea
    ) {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addAttributeSorter', [$aTextArea, Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(
        AttributeInterface $aTextArea,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                TextAreaSorter::class
            )
        )->during('addAttributeSorter', [$aTextArea, 'A_BAD_DIRECTION']);
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_locale_validation(
        $attributeValidatorHelper,
        AttributeInterface $textArea,
        SearchQueryBuilder $sqb
    ) {
        $textArea->getCode()->willReturn('description');
        $textArea->getBackendType()->willReturn('textarea');
        $textArea->isLocaleSpecific()->willReturn(true);
        $textArea->getAvailableLocaleCodes('fr_FR');

        $e = new \LogicException('Attribute "description" expects a locale, none given.');
        $attributeValidatorHelper->validateLocale($textArea, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'description',
                TextAreaSorter::class,
                $e
            )
        )->during(
            'addAttributeSorter',
            [$textArea, Directions::ASCENDING, 'en_US', 'ecommerce', []]
        );
    }

    function it_throws_an_exception_when_an_exception_is_thrown_by_the_attribute_validator_on_scope_validation(
        $attributeValidatorHelper,
        AttributeInterface $textArea,
        SearchQueryBuilder $sqb
    ) {
        $textArea->getCode()->willReturn('description');
        $textArea->getBackendType()->willReturn('textarea');
        $textArea->isScopable()->willReturn(false);

        $e = new \LogicException('Attribute "description" does not expect a scope, "ecommerce" given.');
        $attributeValidatorHelper->validateLocale($textArea, 'en_US')->willThrow($e);

        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'description',
                TextAreaSorter::class,
                $e
            )
        )->during(
            'addAttributeSorter',
            [$textArea, Directions::DESCENDING, 'en_US', 'ecommerce', []]
        );
    }
}

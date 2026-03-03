<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectTranslatorSpec extends ObjectBehavior
{
    function let(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->beConstructedWith($getExistingAttributeOptionsWithValues);
    }

    function it_only_supports_simple_select_attributes()
    {
        $this->supports('pim_catalog_simpleselect', 'collection')->shouldReturn(true);
        $this->supports('pim_catalog_boolean', 'is_activated')->shouldReturn(false);
    }

    function it_translates_simple_select_value_with_its_label(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $locale = 'fr_FR';
        $attributeCode = 'color';

        $redOptionCode = 'red';
        $redOptionKey = $this->optionKey($attributeCode, $redOptionCode);
        $redTranslation = 'rouge';

        $yellowOptionCode = 'yellow';
        $yellowOptionKey = $this->optionKey($attributeCode, $yellowOptionCode);
        $yellowTranslation = 'jaune';

        $optionWithoutTranslationCode = 'purple';
        $optionWithoutTranslationKey = $this->optionKey($attributeCode, $optionWithoutTranslationCode);
        $optionWithoutTranslation = '[purple]';

        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            [$redOptionKey, $yellowOptionKey, $optionWithoutTranslationKey]
        )->willReturn(
            [
                $redOptionKey                => [$locale => $redTranslation],
                $yellowOptionKey             => [$locale => $yellowTranslation],
                $optionWithoutTranslationKey => [$locale => null]
            ]
        );

        $this->translate(
            $attributeCode,
            [],
            [$redOptionCode, $yellowOptionCode, $optionWithoutTranslationCode],
            $locale
        )->shouldReturn([$redTranslation, $yellowTranslation, $optionWithoutTranslation]);
    }

    function it_is_case_insensitive_to_find_option_labels(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $locale = 'fr_FR';
        $getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes([
                $this->optionKey('color', 'red'),
                $this->optionKey('color', 'yellow'),
                $this->optionKey('color', 'purple')
            ])
            ->willReturn([
                $this->optionKey('color', 'red')    => [$locale => 'rouge'],
                $this->optionKey('color', 'yellow') => [$locale => 'jaune'],
                $this->optionKey('color', 'purple') => [$locale => 'purple']
            ]);

        $this->translate('color', [], ['ReD', 'YeLLoW', 'PURPle', ''], $locale)
            ->shouldReturn(['rouge', 'jaune', 'purple', '']);
    }

    function it_is_attribute_code_case_insensitive_to_find_option_labels(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes([
                'color.red',
                'color.yellow',
                'color.purple'
            ])
            ->willReturn([
                'Color.red' => ['fr_FR' => 'rouge'],
                'Color.yellow' => ['fr_FR' => 'jaune'],
                'Color.purple' => ['fr_FR' => 'purple']
            ]);

        $this->translate('Color', [], ['ReD', 'YeLLoW', 'PURPle', ''], 'fr_FR')
            ->shouldReturn(['rouge', 'jaune', 'purple', '']);
    }

    function it_translates_simple_select_value_with_numeric_label(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $locale = 'fr_FR';
        $attributeCode = 'color';

        $redOptionCode = '0';
        $redOptionKey = $this->optionKey($attributeCode, $redOptionCode);
        $redTranslation = 'zero';

        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            [$redOptionKey]
        )->willReturn(
            [
                $redOptionKey => [$locale => $redTranslation],
            ]
        );

        $this->translate(
            $attributeCode,
            [],
            [$redOptionCode],
            $locale
        )->shouldReturn([$redTranslation]);
    }

    function it_puts_the_option_code_between_brackets_when_the_option_does_have_a_translation(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $optionWithoutTranslationKey = $this->optionKey('color', 'purple');
        $getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes([$optionWithoutTranslationKey])
            ->willReturn([$optionWithoutTranslationKey => ['fr_FR' => null]]);

        $this->translate('color', [], ['purple'], 'fr_FR')->shouldReturn(['[purple]']);
    }

    private function optionKey(string $attributeCode, string $optionCode): string
    {
        return sprintf('%s.%s', $attributeCode, $optionCode);
    }
}

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
class MultiSelectTranslatorSpec extends ObjectBehavior
{
    function let(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->beConstructedWith($getExistingAttributeOptionsWithValues);
    }

    function it_only_supports_multi_select_attributes()
    {
        $this->supports('pim_catalog_multiselect', 'collection')->shouldReturn(true);
        $this->supports('other_attribute_type', 'is_activated')->shouldReturn(false);
    }

    function it_translates_multi_select_options_with_their_label(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $locale = 'fr_FR';
        $getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes([
                $this->optionKey('color', 'red'),
                $this->optionKey('color', 'yellow'),
                $this->optionKey('color', 'purple')
            ])
            ->willReturn(
                [
                    $this->optionKey('color', 'red')    => [$locale => 'rouge'],
                    $this->optionKey('color', 'yellow') => [$locale => 'jaune'],
                    $this->optionKey('color', 'purple') => [$locale => 'purple']
                ]
            );

        $this->translate('color', [], ['red,yellow', 'purple', ''], $locale)
            ->shouldReturn(['rouge,jaune', 'purple', '']);
    }

    function it_puts_the_option_code_between_brackets_when_the_option_does_have_a_translation(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $optionWithoutTranslationKey1 = $this->optionKey('color', 'purple');
        $optionWithoutTranslationKey2 = $this->optionKey('color', 'green');
        $optionWithoutTranslationKey3 = $this->optionKey('color', 'red');
        $getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes(
                [$optionWithoutTranslationKey1, $optionWithoutTranslationKey2, $optionWithoutTranslationKey3]
            )
            ->willReturn([
                $optionWithoutTranslationKey1 => ['fr_FR' => null],
                $optionWithoutTranslationKey2 => ['fr_FR' => null],
                $optionWithoutTranslationKey3 => ['fr_FR' => null]
            ]);

        $this->translate('color', [], ['purple,green', 'red'], 'fr_FR')
            ->shouldReturn(['[purple],[green]', '[red]']);
    }

    private function optionKey(string $attributeCode, string $optionCode): string
    {
        return sprintf('%s.%s', $attributeCode, $optionCode);
    }
}

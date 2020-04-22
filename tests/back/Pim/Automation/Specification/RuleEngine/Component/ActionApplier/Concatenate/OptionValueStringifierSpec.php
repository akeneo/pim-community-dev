<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\OptionValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class OptionValueStringifierSpec extends ObjectBehavior
{
    function let(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->beConstructedWith($getExistingAttributeOptionsWithValues, [
            'pim_catalog_simpleselect',
            'pim_catalog_multiselect',
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(OptionValueStringifier::class);
    }

    function it_is_a_stringifier()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldReturn(['pim_catalog_simpleselect', 'pim_catalog_multiselect']);
    }

    function it_cannot_stringify_a_non_option_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('stringify', [
            ScalarValue::value('attribute', 'code'),
            []
        ]);
    }

    function it_stringifies_an_option_value_as_codes(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionValue::value('attribute', 'code');

        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code'])
            ->willReturn(['attribute.code' => ['fr_FR' => 'ma trad', 'en_US' => 'my trad']]);

        $this->stringify($value, [])->shouldReturn('code');
    }

    function it_stringifies_a_multiple_value_as_codes(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionsValue::value('attribute', ['code1', 'code2', 'code3']);

        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code1', 'attribute.code2', 'attribute.code3'])
            ->willReturn([
                'attribute.code2' => ['fr_FR' => 'ma trad2', 'en_US' => 'my trad2'],
                'attribute.code1' => ['fr_FR' => 'ma trad1', 'en_US' => 'my trad1'],
            ]);

        $this->stringify($value, [])->shouldReturn('code1, code2');
    }

    function it_stringifies_an_option_value_with_a_specific_label_locale(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionValue::value('attribute', 'code');
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code'])
            ->willReturn(['attribute.code' => ['fr_FR' => 'ma trad', 'en_US' => 'my trad']]);

        $this->stringify($value, ['label_locale' => 'en_US'])->shouldReturn('my trad');
        $this->stringify($value, ['label_locale' => 'fr_FR'])->shouldReturn('ma trad');
    }


    function it_stringifies_a_multiple_option_value_with_a_specific_label_locale(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionsValue::value('attribute', ['code1', 'code2', 'code3']);
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code1', 'attribute.code2', 'attribute.code3'])
            ->willReturn([
                'attribute.code2' => ['fr_FR' => 'ma trad2', 'en_US' => 'my trad2'],
                'attribute.code1' => ['fr_FR' => 'ma trad1', 'en_US' => 'my trad1'],
            ]);

        $this->stringify($value, ['label_locale' => 'en_US'])->shouldReturn('my trad1, my trad2');
        $this->stringify($value, ['label_locale' => 'fr_FR'])->shouldReturn('ma trad1, ma trad2');
    }

    function it_uses_the_codes_when_stringify_with_an_unknown_label_locale(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionsValue::value('attribute', ['code1', 'code2']);
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code1', 'attribute.code2'])
            ->willReturn([
                'attribute.code2' => ['fr_FR' => 'ma trad2', 'en_US' => 'my trad2'],
                'attribute.code1' => ['fr_FR' => 'ma trad1', 'en_US' => 'my trad1'],
            ]);

        $this->stringify($value, ['label_locale' => 'de_DE'])->shouldReturn('code1, code2');
    }

    function it_stringifies_the_value_with_mixed_translation_and_code_when_translation_does_not_exist(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionsValue::value('attribute', ['code1', 'code2']);
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code1', 'attribute.code2'])
            ->willReturn([
                'attribute.code2' => ['en_US' => 'my trad2'],
                'attribute.code1' => ['fr_FR' => 'ma trad1', 'en_US' => 'my trad1'],
            ]);

        $this->stringify($value, ['label_locale' => 'fr_FR'])->shouldReturn('ma trad1, code2');
    }

    function it_filters_the_results_when_attribute_option_does_not_exist_anymore(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $value = OptionsValue::value('attribute', ['code1', 'code2']);
        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(['attribute.code1', 'attribute.code2'])
            ->willReturn([
                'attribute.code1' => ['fr_FR' => 'ma trad1', 'en_US' => 'my trad1'],
            ]);

        $this->stringify($value, ['label_locale' => 'en_US'])->shouldReturn('my trad1');
    }
}

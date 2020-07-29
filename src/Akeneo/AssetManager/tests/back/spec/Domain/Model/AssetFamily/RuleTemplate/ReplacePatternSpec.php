<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;

use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ReplacePatternSpec extends ObjectBehavior
{
    public function it_tells_if_a_field_has_an_extrapolation()
    {
        $this::isExtrapolation('color')->shouldReturn(false);
        $this::isExtrapolation('{{attribute}}')->shouldReturn(true);
        $this::isExtrapolation(['red', '{{attribute}}', 'blue'])->shouldReturn(true);
        $this::isExtrapolation(['red', 'blue'])->shouldReturn(false);
        $this::isExtrapolation(true)->shouldReturn(false);
        $this::isExtrapolation([true, false])->shouldReturn(false);
    }

    public function it_can_replace_string_properties(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue('code')->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $this::replace('{{ code }}', $accessibleAsset)->shouldReturn('nice_asset');
    }

    public function it_can_replace_array_properties(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue('colors')->willReturn(true);
        $accessibleAsset->getValue('colors')->willReturn(['blue', 'red']);
        $this::replace('{{colors}}', $accessibleAsset)->shouldReturn(['blue', 'red']);
    }

    public function it_can_replace_string_within_an_array_properties(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue('main_color')->willReturn(true);
        $accessibleAsset->getValue('main_color')->willReturn('blue');
        $this::replace(['red', '{{main_color}}'], $accessibleAsset)->shouldReturn(['red', 'blue']);
    }

    public function it_handles_static_array_of_strings()
    {
        $accessibleAsset = new PropertyAccessibleAsset('hat', []);
        $this::replace(['red', 'blue'], $accessibleAsset)->shouldReturn(['red', 'blue']);
    }

    public function it_can_replace_several_properties(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue('code')->willReturn(true);
        $accessibleAsset->hasValue('type')->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $accessibleAsset->getValue('type')->willReturn('image');
        $this::replace('{{code}}-{{type}}', $accessibleAsset)->shouldReturn('nice_asset-image');
    }

    public function it_can_replace_a_list_of_several_properties(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue('code')->willReturn(true);
        $accessibleAsset->hasValue('type')->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $accessibleAsset->getValue('type')->willReturn('image');
        $this::replace(['{{code}}-{{type}}', '{{code}}-{{type}}'], $accessibleAsset)->shouldReturn(['nice_asset-image', 'nice_asset-image']);
    }

    public function it_cannot_replace_several_properties_with_array(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue('code')->willReturn(true);
        $accessibleAsset->hasValue('colors')->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $accessibleAsset->getValue('colors')->willReturn(['blue', 'red']);
        $this->shouldThrow(new \InvalidArgumentException('The asset property "colors" could not be replaced as his value is an array'))->during('replace', ['{{code}}-{{colors}}', $accessibleAsset]);
    }

    public function it_cannot_replace_unknown_properties()
    {
        $accessibleAsset = new PropertyAccessibleAsset('hat', []);
        $this->shouldThrow(new \InvalidArgumentException('The asset property "UNKNOWN" does not exist'))->during('replace', ['{{UNKNOWN}}', $accessibleAsset]);
    }
}

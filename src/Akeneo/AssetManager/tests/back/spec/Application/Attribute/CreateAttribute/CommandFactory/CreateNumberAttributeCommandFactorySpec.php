<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateNumberAttributeCommandFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateNumberAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateNumberAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateNumberAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_number()
    {
        $this->supports(['type' => 'number'])->shouldReturn(true);
        $this->supports(['type' => 'unsupported_type'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_number_attribute()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'picture',
            'labels' => ['fr_FR' => 'Portrait'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'decimals_allowed' => false,
            'min_value' => '0',
            'max_value' => '200'
        ]);

        $command->shouldBeAnInstanceOf(CreateNumberAttributeCommand::class);
        $command->assetFamilyIdentifier->shouldBe('designer');
        $command->code->shouldBe('picture');
        $command->labels->shouldBe(['fr_FR' => 'Portrait']);
        $command->isRequired->shouldBe(false);
        $command->valuePerChannel->shouldBe(false);
        $command->valuePerLocale->shouldBe(false);
        $command->decimalsAllowed->shouldBe(false);
        $command->minValue->shouldBe('0');
        $command->maxValue->shouldBe('200');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_creates_a_command_with_default_properties_if_some_are_missing()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'picture',
            'labels' => ['fr_FR' => 'Portrait'],
        ]);

        $command->shouldBeAnInstanceOf(CreateNumberAttributeCommand::class);
        $command->isRequired->shouldBeEqualTo(false);
        $command->decimalsAllowed->shouldBeEqualTo(false);
        $command->minValue->shouldBe(null);
        $command->maxValue->shouldBe(null);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
    }
}

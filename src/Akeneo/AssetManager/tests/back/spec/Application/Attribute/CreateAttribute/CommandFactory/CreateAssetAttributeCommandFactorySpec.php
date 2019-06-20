<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAssetAttributeCommandFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAssetAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateAssetAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAssetAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_asset()
    {
        $this->supports(['type' => 'asset'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_asset_attribute()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'asset_type' => 'designer',
        ]);

        $command->shouldBeAnInstanceOf(CreateAssetAttributeCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('mentor');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Mentor']);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->assetType->shouldBeEqualTo('designer');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'asset_family_identifier' => 'designer',
            'code' => 'mentor',
            'is_required' => false,
            //'value_per_channel' => false, // For the test purpose, this one is missing
            'value_per_locale' => false,
            'asset_type' => 'designer',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_throws_an_exception_if_there_is_one_missing_additional_property()
    {
        $command = [
            'asset_family_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            // 'asset_type' => 'designer', // For the test purpose, this one is missing
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }
}

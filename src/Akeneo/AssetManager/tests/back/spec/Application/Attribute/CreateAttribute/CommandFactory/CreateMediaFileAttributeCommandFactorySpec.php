<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateMediaFileAttributeCommandFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateMediaFileAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateMediaFileAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_media_file()
    {
        $this->supports(['type' => 'media_file'])->shouldReturn(true);
        $this->supports(['type' => 'text'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_media_file_attribute()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'picture',
            'labels' => ['fr_FR' => 'Portrait'],
            'is_required' => false,
            'is_read_only' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'max_file_size' => '1512.12',
            'allowed_extensions' => ['pdf', 'png'],
        ]);

        $command->shouldBeAnInstanceOf(CreateMediaFileAttributeCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('picture');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Portrait']);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->maxFileSize->shouldBeEqualTo('1512.12');
        $command->allowedExtensions->shouldBeEqualTo(['pdf', 'png']);
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'asset_family_identifier' => 'designer',
//            'code' => 'picture', // For the test purpose, this one is missing
            'is_required' => false,
            'is_read_only' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'max_file_size' => '1512.12',
            'allowed_extensions' => ['pdf', 'png'],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_creates_a_command_with_a_default_properties_if_the_value_is_missing()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'picture',
            'labels' => ['fr_FR' => 'Portrait'],
        ]);

        $command->shouldBeAnInstanceOf(CreateMediaFileAttributeCommand::class);
        $command->isRequired->shouldBeEqualTo(false);
        $command->maxFileSize->shouldBeEqualTo(null);
        $command->allowedExtensions->shouldBeEqualTo([]);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
    }
}

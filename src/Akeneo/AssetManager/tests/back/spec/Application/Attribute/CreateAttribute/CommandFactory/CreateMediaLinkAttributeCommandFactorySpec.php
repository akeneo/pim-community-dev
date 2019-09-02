<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateMediaLinkAttributeCommandFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaLinkAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateMediaLinkAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateMediaLinkAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_media_link()
    {
        $this->supports(['type' => 'media_link'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_media_link_attribute()
    {
        $command = $this->create([
            'asset_family_identifier' => 'packshot',
            'code' => 'full_resolution',
            'labels' => ['en_US' => 'Full resolution'],
            'is_required' => true,
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
            'prefix' => 'http://my.dam/',
            'suffix' => '/full',
        ]);

        $command->shouldBeAnInstanceOf(CreateMediaLinkAttributeCommand::class);

        $command->assetFamilyIdentifier->shouldBeEqualTo('packshot');
        $command->code->shouldBeEqualTo('full_resolution');
        $command->labels->shouldBeEqualTo(['en_US' => 'Full resolution']);
        $command->isRequired->shouldBeEqualTo(true);
        $command->valuePerChannel->shouldBeEqualTo(true);
        $command->valuePerLocale->shouldBeEqualTo(true);
        $command->mediaType->shouldBeEqualTo('image');
        $command->prefix->shouldBeEqualTo('http://my.dam/');
        $command->suffix->shouldBeEqualTo('/full');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'asset_family_identifier' => 'packshot',
            // 'code' => 'full_resolution', // For the test purpose, this one is missing
            'labels' => ['en_US' => 'Full resolution'],
            'is_required' => true,
            'value_per_channel' => true,
            'value_per_locale' => true,
            'media_type' => 'image',
            'prefix' => 'http://my.dam/',
            'suffix' => '/full',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    public function it_builds_the_command_with_some_default_values()
    {
        $command = $this->create([
            'asset_family_identifier' => 'packshot',
            'code' => 'full_resolution',
            'media_type' => 'image',
        ]);

        $command->shouldBeAnInstanceOf(CreateMediaLinkAttributeCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('packshot');
        $command->code->shouldBeEqualTo('full_resolution');
        $command->mediaType->shouldBeEqualTo('image');

        // default values:
        $command->labels->shouldBeEqualTo([]);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->prefix->shouldBeEqualTo(null);
        $command->suffix->shouldBeEqualTo(null);
    }
}

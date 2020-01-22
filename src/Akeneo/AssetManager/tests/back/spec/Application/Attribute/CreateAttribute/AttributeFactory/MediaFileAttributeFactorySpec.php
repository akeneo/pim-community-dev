<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\MediaFileAttributeFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use PhpSpec\ObjectBehavior;

class MediaFileAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaFileAttributeFactory::class);
    }

    function it_only_supports_create_media_file_commands()
    {
        $this->supports(
            new CreateMediaFileAttributeCommand(
                'designer',
                'color',
                [],
                false,
                false,
                false,
                false,
                null,
                [],
                MediaType::IMAGE
            )
        )->shouldReturn(true);
        $this->supports(
            new CreateTextAttributeCommand(
                'designer',
                'color',
                [],
                false,
                false,
                false,
                false,
                null,
                false,
                false,
                null,
                null
            )
        )->shouldReturn(false);
    }

    function it_creates_a_media_file_attribute_with_command()
    {
        $command = new CreateMediaFileAttributeCommand(
            'designer',
            'name',
            [
                'fr_FR' => 'Nom',
            ],
            true,
            false,
            false,
            false,
            '30.0',
            ['pdf', 'png'],
            MediaType::IMAGE
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                => 'name_designer_test',
            'asset_family_identifier'   => 'designer',
            'code'                      => 'name',
            'labels'                    => ['fr_FR' => 'Nom'],
            'order'                     => 0,
            'is_required'               => true,
            'value_per_channel'         => false,
            'value_per_locale'          => false,
            'type'                      => 'media_file',
            'max_file_size'             => '30.0',
            'allowed_extensions'        => ['pdf', 'png'],
            'media_type'                => MediaType::IMAGE
        ]);
    }

    function it_creates_a_media_file_attribute_with_no_max_file_size_limit()
    {
        $command = new CreateMediaFileAttributeCommand(
            'designer',
            'name',
            [
                'fr_FR' => 'Nom',
            ],
            true,
            false,
            false,
            false,
            null,
            ['pdf', 'png'],
            MediaType::IMAGE
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'name_designer_test',
            'asset_family_identifier'     => 'designer',
            'code'                        => 'name',
            'labels'                      => ['fr_FR' => 'Nom'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'media_file',
            'max_file_size'               => null,
            'allowed_extensions'          => ['pdf', 'png'],
            'media_type'                  => MediaType::IMAGE
        ]);
    }

    function it_creates_a_media_file_attribute_with_extensions_all_allowed()
    {
        $command = new CreateMediaFileAttributeCommand(
            'designer',
            'name',
            [
                'fr_FR' => 'Nom',
            ],
            true,
            false,
            false,
            false,
            null,
            AttributeAllowedExtensions::ALL_ALLOWED,
            MediaType::IMAGE
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'name_designer_test',
            'asset_family_identifier'     => 'designer',
            'code'                        => 'name',
            'labels'                      => ['fr_FR' => 'Nom'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'media_file',
            'max_file_size'               => null,
            'allowed_extensions'          => [],
            'media_type'                  => MediaType::IMAGE
        ]);
    }
}

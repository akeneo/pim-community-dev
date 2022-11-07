<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\MediaFileAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;

class MediaFileAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaFileAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_media_file_attributes()
    {
        $this->supports(['attribute_type' => 'media_file'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['wrong_key' => 'wrong_value']]);
    }

    function it_hydrates_a_media_file_attribute_with_no_max_file_size_and_no_extensions()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'picture_designer_fingerprint',
            'code'                       => 'picture',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Image']),
            'attribute_type'             => 'media_file',
            'attribute_order'            => '0',
            'is_required'                => '1',
            'is_read_only'               => '0',
            'value_per_channel'          => '0',
            'value_per_locale'           => '1',
            'wrong_key'                  => '1',
            'additional_properties'      => json_encode([
                'max_file_size'      => null,
                'allowed_extensions' => [],
                'media_type'         => MediaType::IMAGE
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(MediaFileAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'picture_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'picture',
            'labels'                     => ['fr_FR' => 'Image'],
            'order'                      => 0,
            'is_required'                => true,
            'is_read_only'               => false,
            'value_per_channel'          => false,
            'value_per_locale'           => true,
            'type'                       => 'media_file',
            'max_file_size'              => null,
            'allowed_extensions'         => [],
            'media_type'                 => MediaType::IMAGE
        ]);
    }

    function it_hydrates_a_media_file_attribute_with_a_max_file_size_and_with_allowed_extensions()
    {
        $textArea = $this->hydrate([
            'identifier'                 => 'picture_designer_fingerprint',
            'code'                       => 'picture',
            'asset_family_identifier' => 'designer',
            'labels'                     => json_encode(['fr_FR' => 'Image']),
            'attribute_type'             => 'media_file',
            'attribute_order'            => '0',
            'is_required'                => '1',
            'is_read_only'               => '1',
            'value_per_channel'          => '0',
            'value_per_locale'           => '1',
            'wrong_key'                  => '1',
            'additional_properties'      => json_encode([
                'max_file_size'      => '252.12',
                'allowed_extensions' => ['png', 'jpeg'],
                'media_type'         => MediaType::IMAGE
            ]),
        ]);
        $textArea->shouldBeAnInstanceOf(MediaFileAttribute::class);
        $textArea->normalize()->shouldBe([
            'identifier'                 => 'picture_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                       => 'picture',
            'labels'                     => ['fr_FR' => 'Image'],
            'order'                      => 0,
            'is_required'                => true,
            'is_read_only'               => true,
            'value_per_channel'          => false,
            'value_per_locale'           => true,
            'type'                       => 'media_file',
            'max_file_size'              => '252.12',
            'allowed_extensions'         => ['png', 'jpeg'],
            'media_type'                 => MediaType::IMAGE
        ]);
    }
}

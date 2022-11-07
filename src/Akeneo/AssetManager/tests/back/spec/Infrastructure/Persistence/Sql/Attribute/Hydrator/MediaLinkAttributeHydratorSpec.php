<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\MediaLinkAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PhpSpec\ObjectBehavior;

class MediaLinkAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaLinkAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_media_ink_attributes()
    {
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports(['attribute_type' => 'media_link'])->shouldReturn(true);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['wrong_key' => 'wrong_value']]);
    }

    function it_hydrates_an_mediaLink_attribute()
    {
        $mediaLinkAttribute = $this->hydrate([
            'identifier' => 'shooting_ad_fingerprint',
            'code' => 'shooting',
            'asset_family_identifier' => 'ad',
            'labels' => json_encode(['fr_FR' => 'Shooting']),
            'attribute_type' => 'media_link',
            'attribute_order' => '0',
            'is_required' => '1',
            'is_read_only' => '0',
            'value_per_channel' => '0',
            'value_per_locale' => '1',
            'wrong_key' => '1',
            'additional_properties' => json_encode(
                [
                    'media_type' => 'image',
                    'prefix' => 'http://mydam.com/ads/',
                    'suffix' => null,
                ]
            ),
        ]);

        $mediaLinkAttribute->shouldBeAnInstanceOf(MediaLinkAttribute::class);
        $mediaLinkAttribute->normalize()->shouldBe([
            'identifier' => 'shooting_ad_fingerprint',
            'asset_family_identifier' => 'ad',
            'code' => 'shooting',
            'labels' => ['fr_FR' => 'Shooting'],
            'order' => 0,
            'is_required' => true,
            'is_read_only' => false,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'media_link',
            'media_type' => 'image',
            'prefix' => 'http://mydam.com/ads/',
            'suffix' => null,
        ]);
    }
}

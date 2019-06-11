<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator\UrlAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class UrlAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UrlAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_url_attributes()
    {
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports(['attribute_type' => 'url'])->shouldReturn(true);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['wrong_key' => 'wrong_value']]);
    }

    function it_hydrates_an_url_attribute()
    {
        $urlAttribute = $this->hydrate([
            'identifier' => 'shooting_ad_fingerprint',
            'code' => 'shooting',
            'reference_entity_identifier' => 'ad',
            'labels' => json_encode(['fr_FR' => 'Shooting']),
            'attribute_type' => 'url',
            'attribute_order' => '0',
            'is_required' => '1',
            'value_per_channel' => '0',
            'value_per_locale' => '1',
            'wrong_key' => '1',
            'additional_properties' => json_encode(
                [
                    'preview_type' => 'image',
                    'prefix' => 'http://mydam.com/ads/',
                    'suffix' => null,
                ]
            ),
        ]);

        $urlAttribute->shouldBeAnInstanceOf(UrlAttribute::class);
        $urlAttribute->normalize()->shouldBe([
            'identifier' => 'shooting_ad_fingerprint',
            'reference_entity_identifier' => 'ad',
            'code' => 'shooting',
            'labels' => ['fr_FR' => 'Shooting'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'url',
            'preview_type' => 'image',
            'prefix' => 'http://mydam.com/ads/',
            'suffix' => null,
        ]);
    }
}

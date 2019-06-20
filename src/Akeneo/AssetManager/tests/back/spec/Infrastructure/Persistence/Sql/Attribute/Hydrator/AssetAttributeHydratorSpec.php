<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AssetAttributeHydrator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class AssetAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_asset_attributes()
    {
        $this->supports(['attribute_type' => 'asset'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['code' => 'mentor']]);
    }

    function it_hydrates_a_asset_attribute()
    {
        $assetAttribute = $this->hydrate([
            'identifier' => 'mentor_designer_fingerprint',
            'code' => 'mentor',
            'asset_family_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Mentor']),
            'attribute_type' => 'asset',
            'attribute_order' => '0',
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'additional_properties' => json_encode([
                'asset_type' => 'designer',
            ]),
        ]);
        $assetAttribute->shouldBeAnInstanceOf(AssetAttribute::class);
        $assetAttribute->normalize()->shouldBe([
            'identifier' => 'mentor_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => true,
            'type' => 'asset',
            'asset_type' => 'designer',
        ]);
    }
}

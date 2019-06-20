<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator\AssetCollectionAttributeHydrator;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;
use Doctrine\DBAL\Connection;

class AssetCollectionAttributeHydratorSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionAttributeHydrator::class);
    }

    function it_only_supports_the_hydration_of_asset_collection_attributes()
    {
        $this->supports(['attribute_type' => 'asset_collection'])->shouldReturn(true);
        $this->supports(['attribute_type' => 'text'])->shouldReturn(false);
        $this->supports([])->shouldReturn(false);
    }

    function it_throws_if_any_of_the_required_keys_are_not_present_to_hydrate()
    {
        $this->shouldThrow(\RuntimeException::class)->during('hydrate', [['code' => 'brands']]);
    }

    function it_hydrates_a_asset_attribute()
    {
        $assetCollectionAttribute = $this->hydrate([
            'identifier' => 'brands_designer_fingerprint',
            'code' => 'brands',
            'asset_family_identifier' => 'designer',
            'labels' => json_encode(['fr_FR' => 'Marques']),
            'attribute_type' => 'asset',
            'attribute_order' => '0',
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'additional_properties' => json_encode([
                'asset_type' => 'designer',
            ]),
        ]);
        $assetCollectionAttribute->shouldBeAnInstanceOf(AssetCollectionAttribute::class);
        $assetCollectionAttribute->normalize()->shouldBe([
            'identifier' => 'brands_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code' => 'brands',
            'labels' => ['fr_FR' => 'Marques'],
            'order' => 0,
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'asset_collection',
            'asset_type' => 'designer',
        ]);
    }
}

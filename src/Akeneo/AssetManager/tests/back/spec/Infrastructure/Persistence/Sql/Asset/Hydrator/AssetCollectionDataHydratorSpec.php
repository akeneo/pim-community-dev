<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetCollectionData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetCollectionDataHydrator;
use PhpSpec\ObjectBehavior;

class AssetCollectionDataHydratorSpec extends ObjectBehavior
{
    function let(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->beConstructedWith($findCodesByIdentifiers);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_asset_collection_data_attribute(
        AssetAttribute $assetAttribute,
        AssetCollectionAttribute $assetCollectionAttribute
    ) {
        $this->supports($assetAttribute)->shouldReturn(false);
        $this->supports($assetCollectionAttribute)->shouldReturn(true);
    }

    function it_hydrates_asset_collection_data_only_if_the_assets_still_exists(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        AssetCollectionAttribute $assetCollectionAttribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('assetFamilyType');
        $assetCollectionAttribute->getAssetType()->willReturn($assetFamilyIdentifier);

        $findCodesByIdentifiers
            ->find(['phillipe_starck_123456', 'patricia_urquiola_123456'])
            ->willReturn(['phillipe_starck', 'patricia_urquiola']);

        $assetData = $this->hydrate(['phillipe_starck_123456', 'patricia_urquiola_123456'], $assetCollectionAttribute);

        $assetData->shouldBeAnInstanceOf(AssetCollectionData::class);
        $assetData->normalize()->shouldReturn(['phillipe_starck', 'patricia_urquiola']);
    }

    function it_returns_an_empty_data_if_none_of_the_assets_still_exists(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        AssetCollectionAttribute $assetCollectionAttribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('assetFamilyType');
        $assetCollectionAttribute->getAssetType()->willReturn($assetFamilyIdentifier);

        $findCodesByIdentifiers
            ->find(['phillipe_starck_123456', 'patricia_urquiola_123456'])
            ->willReturn([]);

        $assetData = $this->hydrate(['phillipe_starck_123456', 'patricia_urquiola_123456'], $assetCollectionAttribute);
        $assetData->shouldBeAnInstanceOf(EmptyData::class);
    }
}

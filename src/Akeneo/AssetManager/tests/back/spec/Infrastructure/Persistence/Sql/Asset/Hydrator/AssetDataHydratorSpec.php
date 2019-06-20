<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetDataHydrator;
use PhpSpec\ObjectBehavior;

class AssetDataHydratorSpec extends ObjectBehavior
{
    function let(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->beConstructedWith($findCodesByIdentifiers);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_asset_data_attribute(
        AssetAttribute $assetAttribute,
        TextAttribute $textAttribute
    ) {
        $this->supports($assetAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_hydrates_asset_collection_data_only_if_the_assets_still_exists(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        AssetAttribute $assetAttribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('assetFamilyType');
        $assetAttribute->getAssetType()->willReturn($assetFamilyIdentifier);

        $findCodesByIdentifiers
            ->find(['phillipe_starck_123456'])
            ->willReturn(['phillipe_starck']);

        $assetData = $this->hydrate('phillipe_starck_123456', $assetAttribute);
        $assetData->shouldBeAnInstanceOf(AssetData::class);
        $assetData->normalize()->shouldReturn('phillipe_starck');
    }

    function it_returns_an_empty_data_if_the_assets_does_not_exists_anymore(
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        AssetAttribute $assetAttribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('assetFamilyType');
        $assetAttribute->getAssetType()->willReturn($assetFamilyIdentifier);

        $findCodesByIdentifiers
            ->find(['phillipe_starck_123456'])
            ->willReturn([]);

        $assetData = $this->hydrate('phillipe_starck_123456', $assetAttribute);
        $assetData->shouldBeAnInstanceOf(EmptyData::class);
    }
}

<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCollectionValueCommandFactory;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use PhpSpec\ObjectBehavior;

class EditAssetCollectionValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditAssetCollectionValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_asset_collection_attribute(
        AssetAttribute $assetAttribute,
        AssetCollectionAttribute $assetCollectionAttribute
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => ['philippe_starck', 'patricia_urquiola']
        ];

        $this->supports($assetAttribute, $normalizedValue)->shouldReturn(false);
        $this->supports($assetCollectionAttribute, $normalizedValue)->shouldReturn(true);
    }

    function it_only_supports_values_with_an_not_empty_array_as_data(AssetCollectionAttribute $assetCollectionAttribute)
    {
        $this->supports($assetCollectionAttribute, ['data' => []])->shouldReturn(false);
        $this->supports($assetCollectionAttribute, ['data' => 'starck'])->shouldReturn(false);
    }

    function it_creates_asset_collection_value(AssetCollectionAttribute $assetAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => ['philippe_starck', 'patricia_urquiola']
        ];
        $command = $this->create($assetAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditAssetCollectionValueCommand::class);
        $command->attribute->shouldBeEqualTo($assetAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->assetCodes->shouldBeEqualTo( ['philippe_starck', 'patricia_urquiola']);
    }
}

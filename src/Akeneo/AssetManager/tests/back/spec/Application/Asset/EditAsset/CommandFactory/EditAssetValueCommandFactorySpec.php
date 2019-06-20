<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetValueCommandFactory;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use PhpSpec\ObjectBehavior;

class EditAssetValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditAssetValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_asset_attribute(
        AssetAttribute $assetAttribute,
        AssetCollectionAttribute $assetCollectionAttribute
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'philippe_starck'
        ];

        $this->supports($assetAttribute, $normalizedValue)->shouldReturn(true);
        $this->supports($assetCollectionAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_creates_asset_value(AssetAttribute $assetAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'philippe_starck'
        ];
        $command = $this->create($assetAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditAssetValueCommand::class);
        $command->attribute->shouldBeEqualTo($assetAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->assetCode->shouldBeEqualTo('philippe_starck');
    }
}

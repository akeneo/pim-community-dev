<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\AssetCollectionUpdater;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\AssetUpdater;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCollectionUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionUpdater::class);
    }

    function it_only_supports_edit_asset_collection_value_command(
        EditAssetValueCommand $editAssetValueCommand,
        EditAssetCollectionValueCommand $editAssetCollectionValueCommand
    ) {
        $this->supports($editAssetValueCommand)->shouldReturn(false);
        $this->supports($editAssetCollectionValueCommand)->shouldReturn(true);
    }

    function it_edits_the_asset_collection_value_of_a_asset(Asset $asset) {
        $assetAttribute = $this->getAttribute();

        $editAssetCollectionValueCommand = new EditAssetCollectionValueCommand(
            $assetAttribute,
            'ecommerce',
            'fr_FR',
            ['cogip', 'sbep']
        );
        $value = Value::create(
            $editAssetCollectionValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editAssetCollectionValueCommand->channel),
            LocaleReference::createfromNormalized($editAssetCollectionValueCommand->locale),
            AssetCollectionData::createFromNormalize($editAssetCollectionValueCommand->assetCodes)
        );

        $this->__invoke($asset, $editAssetCollectionValueCommand);
        $asset->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(Asset $asset, EditAssetValueCommand $editAssetValueCommand)
    {
        $this->supports($editAssetValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$asset, $editAssetValueCommand]);
    }

    private function getAttribute(): AssetCollectionAttribute
    {
        $assetAttribute = AssetCollectionAttribute::create(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AssetFamilyIdentifier::fromString('brand')
        );

        return $assetAttribute;
    }
}

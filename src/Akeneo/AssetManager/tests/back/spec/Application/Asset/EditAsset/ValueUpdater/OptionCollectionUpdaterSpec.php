<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\OptionCollectionUpdater;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\ValueUpdaterInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

class OptionCollectionUpdaterSpec extends ObjectBehavior
{
    function it_is_a_value_updater()
    {
        $this->shouldBeAnInstanceOf(ValueUpdaterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(OptionCollectionUpdater::class);
    }

    function it_supports_the_edit_option_collection_value_command(
        EditOptionCollectionValueCommand $editOptionValueCommand,
        EditTextValueCommand $editTextValueCommand
    ) {
        $this->supports($editOptionValueCommand)->shouldReturn(true);
        $this->supports($editTextValueCommand)->shouldReturn(false);
    }

    function it_updates_a_asset_with_an_option_collection_value(Asset $asset)
    {
        $attribute = $this->getAttribute();

        $command = new EditOptionCollectionValueCommand(
            $attribute,
            'mobile',
            'en_US',
            ['18-25', '26-40']
        );

        $value = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionCollectionData::createFromNormalize(['18-25', '26-40'])
        );

        $asset->setValue($value)->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    private function getAttribute(): OptionCollectionAttribute
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create('brand', 'age', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('age_target'),
            LabelCollection::fromArray(['fr_FR' => 'Cible âge', 'en_US' => 'Age target']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );

        return $optionAttribute;
    }
}

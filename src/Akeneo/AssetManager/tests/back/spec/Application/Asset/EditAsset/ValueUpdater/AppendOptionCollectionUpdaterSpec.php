<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AppendOptionCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\AppendOptionCollectionUpdater;
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
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;

class AppendOptionCollectionUpdaterSpec extends ObjectBehavior
{
    function it_is_a_value_updater()
    {
        $this->shouldBeAnInstanceOf(ValueUpdaterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(AppendOptionCollectionUpdater::class);
    }

    function it_supports_the_append_option_collection_value_command(
        EditOptionCollectionValueCommand $editOptionValueCommand,
        AppendOptionCollectionValueCommand $appendOptionValueCommand
    ) {
        $this->supports($appendOptionValueCommand)->shouldReturn(true);
        $this->supports($editOptionValueCommand)->shouldReturn(false);
    }

    function it_append_option_collection_value_on_existing_value(Asset $asset)
    {
        $attribute = $this->getAttribute();

        $command = new AppendOptionCollectionValueCommand(
            $attribute,
            'mobile',
            'en_US',
            ['18-25', '26-41']
        );

        $keyValue = ValueKey::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US')
        );

        $existingValue = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionCollectionData::createFromNormalize(['18-25', '26-40'])
        );

        $asset->findValue($keyValue)->shouldBeCalled()->willReturn($existingValue);

        $expectingNewValue = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionCollectionData::createFromNormalize(['18-25', '26-40', '26-41'])
        );

        $asset->setValue($expectingNewValue)->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    function it_add_option_collection_value_on_non_existing_value(Asset $asset)
    {
        $attribute = $this->getAttribute();

        $command = new AppendOptionCollectionValueCommand(
            $attribute,
            'mobile',
            'en_US',
            ['18-25', '26-41']
        );

        $keyValue = ValueKey::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US')
        );

        $asset->findValue($keyValue)->shouldBeCalled()->willReturn(null);

        $expectingNewValue = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionCollectionData::createFromNormalize(['18-25', '26-41'])
        );

        $asset->setValue($expectingNewValue)->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    function it_updates_a_asset_with_an_option_collection_value(Asset $asset)
    {
        $attribute = $this->getAttribute();

        $command = new AppendOptionCollectionValueCommand(
            $attribute,
            'mobile',
            'en_US',
            ['18-25', '26-41']
        );

        $keyValue = ValueKey::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US')
        );

        $existingValue = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionCollectionData::createFromNormalize(['18-25', '26-40'])
        );

        $asset->findValue($keyValue)->shouldBeCalled()->willReturn($existingValue);

        $expectingNewValue = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionCollectionData::createFromNormalize(['18-25', '26-40', '26-41'])
        );

        $asset->setValue($expectingNewValue)->shouldBeCalled();

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

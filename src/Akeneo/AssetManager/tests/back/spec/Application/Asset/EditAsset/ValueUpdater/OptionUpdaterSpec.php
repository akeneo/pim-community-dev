<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\OptionUpdater;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\ValueUpdaterInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

class OptionUpdaterSpec extends ObjectBehavior
{
    function it_is_a_value_updater()
    {
        $this->shouldBeAnInstanceOf(ValueUpdaterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(OptionUpdater::class);
    }

    function it_supports_the_edit_option_value_command(
        EditOptionValueCommand $editOptionValueCommand,
        EditTextValueCommand $editTextValueCommand
    ) {
        $this->supports($editOptionValueCommand)->shouldReturn(true);
        $this->supports($editTextValueCommand)->shouldReturn(false);
    }

    function it_updates_a_asset_with_an_option_value(Asset $asset)
    {
        $attribute = $this->getAttribute();

        $command = new EditOptionValueCommand(
            $attribute,
            'mobile',
            'en_US',
            '18-25'
        );

        $value = Value::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::createFromNormalized('en_US'),
            OptionData::createFromNormalize('18-25')
        );

        $asset->setValue($value)->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    private function getAttribute(): OptionAttribute
    {
        $optionAttribute = OptionAttribute::create(
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

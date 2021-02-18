<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditNumberValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\NumberUpdater;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

class NumberUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberUpdater::class);
    }

    function it_only_supports_edit_number_value_command(
        EditMediaFileValueCommand $editMediaFileValueCommand,
        EditNumberValueCommand $editNumberValueCommand
    ) {
        $this->supports($editMediaFileValueCommand)->shouldReturn(false);
        $this->supports($editNumberValueCommand)->shouldReturn(true);
    }

    function it_edits_the_number_value_of_a_asset(Asset $asset)
    {
        $numberAttribute = $this->getAttribute();

        $editNumberValueCommand = new EditNumberValueCommand(
            $numberAttribute,
            'ecommerce',
            'fr_FR',
            'A name'
        );
        $value = Value::create(
            $editNumberValueCommand->attribute->getIdentifier(),
            ChannelReference::createFromNormalized($editNumberValueCommand->channel),
            LocaleReference::createFromNormalized($editNumberValueCommand->locale),
            NumberData::createFromNormalize($editNumberValueCommand->number)
        );

        $this->__invoke($asset, $editNumberValueCommand);
        $asset->setValue($value)->shouldBeCalled();
    }

    private function getAttribute(): NumberAttribute
    {
        return NumberAttribute::create(
            AttributeIdentifier::create('designer', 'age', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('age'),
            LabelCollection::fromArray(['fr_FR' => 'Age', 'en_US' => 'Age']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::limitless(),
            AttributeLimit::limitless()
        );
    }
}

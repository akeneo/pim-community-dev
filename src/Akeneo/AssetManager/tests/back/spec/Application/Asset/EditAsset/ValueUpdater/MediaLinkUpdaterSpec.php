<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\MediaLinkUpdater;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaLinkUpdater::class);
    }

    function it_only_supports_edit_media_link_value_command(
        EditMediaFileValueCommand $editMediaFileValueCommand,
        EditMediaLinkValueCommand $editTextValueCommand
    ) {
        $this->supports($editMediaFileValueCommand)->shouldReturn(false);
        $this->supports($editTextValueCommand)->shouldReturn(true);
    }

    function it_edits_the_media_link_value_of_a_asset(Asset $asset)
    {
        $mediaLinkAttribute = $this->mediaLinkAttribute();

        $editTextValueCommand = new EditMediaLinkValueCommand(
            $mediaLinkAttribute,
            'ecommerce',
            'fr_FR',
            'house_255121'
        );
        $value = Value::create(
            $editTextValueCommand->attribute->getIdentifier(),
            ChannelReference::createFromNormalized($editTextValueCommand->channel),
            LocaleReference::createFromNormalized($editTextValueCommand->locale),
            MediaLinkData::createFromNormalize($editTextValueCommand->mediaLink)
        );

        $this->__invoke($asset, $editTextValueCommand);
        $asset->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(
        Asset $asset,
        EditMediaFileValueCommand $editMediaFileValueCommand
    ) {
        $this->supports($editMediaFileValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$asset, $editMediaFileValueCommand]);
    }

    private function mediaLinkAttribute(): MediaLinkAttribute
    {
        return MediaLinkAttribute::create(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaType::fromString('image')
        );
    }
}

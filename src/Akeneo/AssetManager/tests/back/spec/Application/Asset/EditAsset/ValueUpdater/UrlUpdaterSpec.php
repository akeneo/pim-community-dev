<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditStoredFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditUrlValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\UrlUpdater;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\Url\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\Url\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\Url\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\UrlAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\UrlData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UrlUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UrlUpdater::class);
    }

    function it_only_supports_edit_url_value_command(
        EditStoredFileValueCommand $editStoredFileValueCommand,
        EditUrlValueCommand $editTextValueCommand
    ) {
        $this->supports($editStoredFileValueCommand)->shouldReturn(false);
        $this->supports($editTextValueCommand)->shouldReturn(true);
    }

    function it_edits_the_url_value_of_a_asset(Asset $asset) {
        $urlAttribute = $this->urlAttribute();

        $editTextValueCommand = new EditUrlValueCommand(
            $urlAttribute,
            'ecommerce',
            'fr_FR',
            'house_255121'
        );
        $value = Value::create(
            $editTextValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editTextValueCommand->channel),
            LocaleReference::createfromNormalized($editTextValueCommand->locale),
            UrlData::createFromNormalize($editTextValueCommand->url)
        );

        $this->__invoke($asset, $editTextValueCommand);
        $asset->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(
        Asset $asset,
        EditStoredFileValueCommand $editStoredFileValueCommand
    ) {
        $this->supports($editStoredFileValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$asset, $editStoredFileValueCommand]);
    }

    private function urlAttribute(): UrlAttribute
    {
        return UrlAttribute::create(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            Prefix::empty(),
            Suffix::empty(),
            MediaType::fromString('image')
        );
    }
}

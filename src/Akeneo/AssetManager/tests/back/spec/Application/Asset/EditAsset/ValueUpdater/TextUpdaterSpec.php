<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditStoredFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\TextUpdater;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TextUpdater::class);
    }

    function it_only_supports_edit_text_value_command(
        EditStoredFileValueCommand $editStoredFileValueCommand,
        EditTextValueCommand $editTextValueCommand
    ) {
        $this->supports($editStoredFileValueCommand)->shouldReturn(false);
        $this->supports($editTextValueCommand)->shouldReturn(true);
    }

    function it_edits_the_text_value_of_a_asset(Asset $asset) {
        $textAttribute = $this->getAttribute();

        $editTextValueCommand = new EditTextValueCommand(
            $textAttribute,
            'ecommerce',
            'fr_FR',
            'A name'
        );
        $value = Value::create(
            $editTextValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editTextValueCommand->channel),
            LocaleReference::createfromNormalized($editTextValueCommand->locale),
            TextData::createFromNormalize($editTextValueCommand->text)
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

    private function getAttribute(): TextAttribute
    {
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        return $textAttribute;
    }
}

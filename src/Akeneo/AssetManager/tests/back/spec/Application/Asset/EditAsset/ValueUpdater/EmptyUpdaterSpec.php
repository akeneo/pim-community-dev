<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EmptyValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\EmptyUpdater;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EmptyUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EmptyUpdater::class);
    }

    function it_only_supports_empty_value_command(
        EmptyValueCommand $emptyValueCommand,
        EditTextValueCommand $editTextValueCommand
    ) {
        $this->supports($emptyValueCommand)->shouldReturn(true);
        $this->supports($editTextValueCommand)->shouldReturn(false);
    }

    function it_empty_value_of_a_asset(
        Asset $asset
    ) {
        $textAttribute = $this->getAttribute();

        $editEmptyValueCommand = new EmptyValueCommand($textAttribute, 'ecommerce', 'fr_FR');

        $value = Value::create(
            $editEmptyValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editEmptyValueCommand->channel),
            LocaleReference::createfromNormalized($editEmptyValueCommand->locale),
            EmptyData::create()
        );
        $this->__invoke($asset, $editEmptyValueCommand);
        $asset->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(Asset $asset, EditTextValueCommand $editTextValueCommand)
    {
        $this->supports($editTextValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$asset, $editTextValueCommand]);
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

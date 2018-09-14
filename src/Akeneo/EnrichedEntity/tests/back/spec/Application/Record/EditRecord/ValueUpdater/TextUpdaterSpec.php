<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditFileValueCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater\TextUpdater;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_only_supports_edit_text_value_command()
    {
        $this->supports(new EditFileValueCommand())->shouldReturn(false);
        $this->supports(new EditTextValueCommand())->shouldReturn(true);
    }

    function it_edits_the_text_value_of_a_record(Record $record) {
        $textAttribute = $this->getAttribute();

        $editTextValueCommand = new EditTextValueCommand();
        $editTextValueCommand->attribute = $textAttribute;
        $editTextValueCommand->channel = 'ecommerce';
        $editTextValueCommand->locale = 'fr_FR';
        $editTextValueCommand->data = 'A name';
        $value = Value::create(
            $editTextValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editTextValueCommand->channel),
            LocaleReference::createfromNormalized($editTextValueCommand->locale),
            TextData::createFromNormalize($editTextValueCommand->data)
        );

        $this->__invoke($record, $editTextValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    function it_edits_the_text_value_of_a_record_with_empty_value(Record $record) {
        $textAttribute = $this->getAttribute();

        $editTextValueCommand = new EditTextValueCommand();
        $editTextValueCommand->attribute = $textAttribute;
        $editTextValueCommand->channel = null;
        $editTextValueCommand->locale = null;
        $editTextValueCommand->data = null;
        $value = Value::create(
            $editTextValueCommand->attribute->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            EmptyData::create()
        );

        $this->__invoke($record, $editTextValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    private function getAttribute(): TextAttribute
    {
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
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

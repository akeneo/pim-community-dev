<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditNumberValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUploadedFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\NumberUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsDecimal;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\NumberData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

class NumberUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NumberUpdater::class);
    }

    function it_only_supports_edit_number_value_command(
        EditUploadedFileValueCommand $editUploadedFileValueCommand,
        EditNumberValueCommand $editNumberValueCommand
    ) {
        $this->supports($editUploadedFileValueCommand)->shouldReturn(false);
        $this->supports($editNumberValueCommand)->shouldReturn(true);
    }

    function it_edits_the_number_value_of_a_record(Record $record) {
        $numberAttribute = $this->getAttribute();

        $editNumberValueCommand = new EditNumberValueCommand(
            $numberAttribute,
            'ecommerce',
            'fr_FR',
            'A name'
        );
        $value = Value::create(
            $editNumberValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editNumberValueCommand->channel),
            LocaleReference::createfromNormalized($editNumberValueCommand->locale),
            NumberData::createFromNormalize($editNumberValueCommand->number)
        );

        $this->__invoke($record, $editNumberValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    private function getAttribute(): NumberAttribute
    {
        $textAttribute = NumberAttribute::create(
            AttributeIdentifier::create('designer', 'age', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('age'),
            LabelCollection::fromArray(['fr_FR' => 'Age', 'en_US' => 'Age']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeIsDecimal::fromBoolean(false)
        );

        return $textAttribute;
    }
}

<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCollectionValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\RecordUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordUpdater::class);
    }

    function it_only_supports_edit_record_value_command(
        EditRecordValueCommand $editRecordValueCommand,
        EditRecordCollectionValueCommand $editRecordCollectionValueCommand
    ) {
        $this->supports($editRecordValueCommand)->shouldReturn(true);
        $this->supports($editRecordCollectionValueCommand)->shouldReturn(false);
    }

    function it_edits_the_record_value_of_a_record(Record $record)
    {
        $recordAttribute = $this->getAttribute();

        $editRecordValueCommand = new EditRecordValueCommand(
            $recordAttribute,
            'ecommerce',
            'fr_FR',
            'cogip'
        );
        $value = Value::create(
            $editRecordValueCommand->attribute->getIdentifier(),
            ChannelReference::createFromNormalized($editRecordValueCommand->channel),
            LocaleReference::createFromNormalized($editRecordValueCommand->locale),
            RecordData::createFromNormalize($editRecordValueCommand->recordCode)
        );

        $this->__invoke($record, $editRecordValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(
        Record $record,
        EditRecordCollectionValueCommand $editRecordCollectionValueCommand
    ) {
        $this->supports($editRecordCollectionValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$record, $editRecordCollectionValueCommand]);
    }

    private function getAttribute(): RecordAttribute
    {
        $recordAttribute = RecordAttribute::create(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            ReferenceEntityIdentifier::fromString('brand')
        );

        return $recordAttribute;
    }
}

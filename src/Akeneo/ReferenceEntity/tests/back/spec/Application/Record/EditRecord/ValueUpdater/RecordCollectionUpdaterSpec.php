<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCollectionValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\RecordCollectionUpdater;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\RecordUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionUpdaterSpec extends ObjectBehavior
{
    function let(FindIdentifiersByReferenceEntityAndCodesInterface $findIdentifiersByReferenceEntityAndCodes)
    {
        $this->beConstructedWith($findIdentifiersByReferenceEntityAndCodes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionUpdater::class);
    }

    function it_only_supports_edit_record_collection_value_command(
        EditRecordValueCommand $editRecordValueCommand,
        EditRecordCollectionValueCommand $editRecordCollectionValueCommand
    ) {
        $this->supports($editRecordValueCommand)->shouldReturn(false);
        $this->supports($editRecordCollectionValueCommand)->shouldReturn(true);
    }

    function it_edits_the_record_collection_value_of_a_record(
        FindIdentifiersByReferenceEntityAndCodesInterface $findIdentifiersByReferenceEntityAndCodes,
        Record $record,
        RecordIdentifier $cogipIdentifier,
        RecordIdentifier $sbepIdentifier
    ) {
        $recordAttribute = $this->getAttribute();

        $editRecordCollectionValueCommand = new EditRecordCollectionValueCommand(
            $recordAttribute,
            'ecommerce',
            'fr_FR',
            ['cogip', 'sbep']
        );
        $value = Value::create(
            $editRecordCollectionValueCommand->attribute->getIdentifier(),
            ChannelReference::createfromNormalized($editRecordCollectionValueCommand->channel),
            LocaleReference::createfromNormalized($editRecordCollectionValueCommand->locale),
            RecordCollectionData::createFromNormalize([
                'cogip_abcdef123456789',
                'sbep_abcdef123456789',
            ])
        );

        $findIdentifiersByReferenceEntityAndCodes->find(
            ReferenceEntityIdentifier::fromString('brand'),
            Argument::any()
        )->willReturn([
            'cogip' => $cogipIdentifier,
            'sbep' => $sbepIdentifier,
        ]);

        $cogipIdentifier->normalize()->willReturn('cogip_abcdef123456789');
        $sbepIdentifier->normalize()->willReturn('sbep_abcdef123456789');

        $this->__invoke($record, $editRecordCollectionValueCommand);
        $record->setValue($value)->shouldBeCalled();
    }

    function it_throws_if_it_does_not_support_the_command(Record $record, EditRecordValueCommand $editRecordValueCommand)
    {
        $this->supports($editRecordValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$record, $editRecordValueCommand]);
    }

    private function getAttribute(): RecordCollectionAttribute
    {
        $recordAttribute = RecordCollectionAttribute::create(
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

<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditOptionValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\OptionUpdater;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\ValueUpdaterInterface;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
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

    function it_updates_a_record_with_an_option_value(Record $record)
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

        $record->setValue($value)->shouldBeCalled();

        $this->__invoke($record, $command);
    }

    private function getAttribute(): OptionAttribute
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::create('brand', 'age', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('age_target'),
            LabelCollection::fromArray(['fr_FR' => 'Cible âge', 'en_US' => 'Age target']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );

        return $optionAttribute;
    }
}

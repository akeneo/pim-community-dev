<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use PhpSpec\ObjectBehavior;

class EditRecordValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_record_attribute(
        RecordAttribute $recordAttribute,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'philippe_starck'
        ];

        $this->supports($recordAttribute, $normalizedValue)->shouldReturn(true);
        $this->supports($recordCollectionAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_creates_record_value(RecordAttribute $recordAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'philippe_starck'
        ];
        $command = $this->create($recordAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditRecordValueCommand::class);
        $command->attribute->shouldBeEqualTo($recordAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->recordCode->shouldBeEqualTo('philippe_starck');
    }
}

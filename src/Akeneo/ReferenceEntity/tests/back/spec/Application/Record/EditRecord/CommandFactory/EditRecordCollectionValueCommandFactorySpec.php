<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCollectionValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCollectionValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use PhpSpec\ObjectBehavior;

class EditRecordCollectionValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordCollectionValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_record_collection_attribute(
        RecordAttribute $recordAttribute,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => ['philippe_starck', 'patricia_urquiola']
        ];

        $this->supports($recordAttribute, $normalizedValue)->shouldReturn(false);
        $this->supports($recordCollectionAttribute, $normalizedValue)->shouldReturn(true);
    }

    function it_only_supports_values_with_an_not_empty_array_as_data(RecordCollectionAttribute $recordCollectionAttribute)
    {
        $this->supports($recordCollectionAttribute, ['data' => []])->shouldReturn(false);
        $this->supports($recordCollectionAttribute, ['data' => 'starck'])->shouldReturn(false);
    }

    function it_creates_record_collection_value(RecordCollectionAttribute $recordAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => ['philippe_starck', 'patricia_urquiola']
        ];
        $command = $this->create($recordAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditRecordCollectionValueCommand::class);
        $command->attribute->shouldBeEqualTo($recordAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->recordCodes->shouldBeEqualTo( ['philippe_starck', 'patricia_urquiola']);
    }
}

<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\Record\EditRecord;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditRecordHandlerSpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository)
    {
        $this->beConstructedWith($recordRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditRecordHandler::class);
    }

    function it_edits_a_record(
        RecordRepositoryInterface $recordRepository,
        EditRecordCommand $editRecordCommand,
        Record $record
    ) {
        $editRecordCommand->identifier = 'sony';
        $editRecordCommand->enrichedEntityIdentifier = 'brand';
        $editRecordCommand->labels = [
            'fr_FR' => 'Sony',
            'en_US' => 'Sony',
        ];

        $recordRepository->getByIdentifier(
            Argument::type(RecordIdentifier::class)
        )->willReturn($record);

        $record->updateLabels(Argument::type(LabelCollection::class))->shouldBeCalled();
        $recordRepository->save($record)->shouldBeCalled();

        $this->__invoke($editRecordCommand);
    }
}

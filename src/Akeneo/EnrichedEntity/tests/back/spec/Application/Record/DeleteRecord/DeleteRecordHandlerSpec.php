<?php

namespace spec\Akeneo\EnrichedEntity\Application\Record\DeleteRecord;

use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteRecordHandlerSpec extends ObjectBehavior
{
    public function let(RecordRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteRecordHandler::class);
    }

    function it_deletes_a_record_by_its_code_and_entity_identifier(RecordRepositoryInterface $repository)
    {
        $command = new DeleteRecordCommand();
        $command->recordCode = 'record_code';
        $command->enrichedEntityIdentifier = 'entity_identifier';

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('entity_identifier');
        $recordCode = RecordCode::fromString('record_code');

        $repository->deleteByEnrichedEntityAndCode($enrichedEntityIdentifier, $recordCode)->shouldBeCalled();

        $this->__invoke($command);
    }
}

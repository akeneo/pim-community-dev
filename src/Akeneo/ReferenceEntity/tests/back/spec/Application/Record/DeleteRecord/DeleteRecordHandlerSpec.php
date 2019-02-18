<?php

namespace spec\Akeneo\ReferenceEntity\Application\Record\DeleteRecord;

use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
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
        $command = new DeleteRecordCommand(
            'record_code',
            'entity_identifier'
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('entity_identifier');
        $recordCode = RecordCode::fromString('record_code');

        $repository->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode)->shouldBeCalled();

        $this->__invoke($command);
    }
}

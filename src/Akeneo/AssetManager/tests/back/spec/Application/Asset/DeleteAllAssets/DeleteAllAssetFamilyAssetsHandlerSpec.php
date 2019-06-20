<?php

namespace spec\Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords;

use Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords\DeleteAllReferenceEntityRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords\DeleteAllReferenceEntityRecordsHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;

class DeleteAllReferenceEntityRecordsHandlerSpec extends ObjectBehavior
{
    public function let(RecordRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteAllReferenceEntityRecordsHandler::class);
    }

    function it_deletes_all_entity_records_by_their_entity_identifier(RecordRepositoryInterface $repository)
    {
        $command = new DeleteAllReferenceEntityRecordsCommand(
            'entity_identifier'
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('entity_identifier');

        $repository->deleteByReferenceEntity($referenceEntityIdentifier)->shouldBeCalled();

        $this->__invoke($command);
    }
}

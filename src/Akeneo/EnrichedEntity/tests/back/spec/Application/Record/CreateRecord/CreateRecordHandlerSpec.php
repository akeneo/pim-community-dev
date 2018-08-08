<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\Record\CreateRecord;

use Akeneo\EnrichedEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateRecordHandlerSpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository)
    {
        $this->beConstructedWith($recordRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateRecordHandler::class);
    }

    function it_creates_and_save_a_new_record(
        RecordRepositoryInterface $recordRepository,
        CreateRecordCommand $createRecordCommand
    ) {
        $createRecordCommand->identifier = [
            'identifier' => 'intel',
            'enriched_entity_identifier' => 'brand'
        ];
        $createRecordCommand->code = 'intel';
        $createRecordCommand->enrichedEntityIdentifier = 'brand';
        $createRecordCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $recordRepository->create(Argument::type(Record::class))->shouldBeCalled();

        $this->__invoke($createRecordCommand);
    }
}

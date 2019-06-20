<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\RefreshRecords;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use PhpSpec\ObjectBehavior;

class RefreshRecordSpec extends ObjectBehavior
{
    function let(RecordRepositoryInterface $recordRepository)
    {
        $this->beConstructedWith($recordRepository);
    }

    function it_refreshes_a_record_by_loading_it_and_updating_it(
        RecordRepositoryInterface $recordRepository,
        Record $recordToRefresh
    ) {
        $recordIdentifier = RecordIdentifier::fromString('a_record_to_refresh');
        $recordRepository->getByIdentifier($recordIdentifier)->willReturn($recordToRefresh);
        $recordRepository->update($recordToRefresh)->shouldBeCalled();

        $this->refresh($recordIdentifier);
    }
}

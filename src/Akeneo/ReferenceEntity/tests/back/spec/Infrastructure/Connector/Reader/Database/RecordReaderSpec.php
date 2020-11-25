<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Reader\Database;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordIdentifiersByReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Reader\Database\RecordReader;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class RecordReaderSpec extends ObjectBehavior
{
    function let(
        FindRecordIdentifiersByReferenceEntityInterface $findRecordIdentifiers,
        RecordRepositoryInterface $recordRepository,
        StepExecution $stepExecution,
        JobParameters $parameters
    ) {
        $this->beConstructedWith($findRecordIdentifiers, $recordRepository);

        $parameters->get('reference_entity_identifier')->willReturn('brand');
        $stepExecution->getJobParameters()->willReturn($parameters);
        $this->setStepExecution($stepExecution);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $findRecordIdentifiers->find($referenceEntityIdentifier)->willReturn(
            new \ArrayIterator(
                [
                    RecordIdentifier::fromString('record_brand_1'),
                    RecordIdentifier::fromString('record_brand_2'),
                    RecordIdentifier::fromString('record_brand_3'),
                ]
            )
        );
        $this->initialize();
    }

    function it_is_an_item_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
    }

    function it_is_a_database_record_reader()
    {
        $this->shouldHaveType(RecordReader::class);
    }

    function it_reads_items_from_database(
        RecordRepositoryInterface $recordRepository,
        StepExecution $stepExecution
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        [$record1, $record2, $record3] = [
            $this->createRecord(RecordIdentifier::fromString('record_brand_1'), $referenceEntityIdentifier),
            $this->createRecord(RecordIdentifier::fromString('record_brand_2'), $referenceEntityIdentifier),
            $this->createRecord(RecordIdentifier::fromString('record_brand_3'), $referenceEntityIdentifier),
        ];
        $recordRepository->getByIdentifier(RecordIdentifier::fromString('record_brand_1'))->willReturn($record1);
        $recordRepository->getByIdentifier(RecordIdentifier::fromString('record_brand_2'))->willReturn($record2);
        $recordRepository->getByIdentifier(RecordIdentifier::fromString('record_brand_3'))->willReturn($record3);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);

        $this->read()->shouldReturn($record1);
        $this->read()->shouldReturn($record2);
        $this->read()->shouldReturn($record3);
        $this->read()->shouldReturn(null);
    }

    private function createRecord(RecordIdentifier $identifier, ReferenceEntityIdentifier $referenceEntityIdentifier): Record
    {
        return Record::create(
            $identifier,
            $referenceEntityIdentifier,
            RecordCode::fromString($identifier->__toString()),
            ValueCollection::fromValues([])
        );
    }
}

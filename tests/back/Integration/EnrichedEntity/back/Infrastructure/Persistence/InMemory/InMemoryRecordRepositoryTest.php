<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\InMemory;

use AkeneoEnterprise\Test\Acceptance\EnrichedEntity\InMemoryRecordRepository;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\RecordRepository;
use PHPUnit\Framework\TestCase;

class InMemoryRecordRepositoryTest extends TestCase
{
    /** @var RecordRepository */
    private $recordRepository;

    public function setup()
    {
        $this->recordRepository = new InMemoryRecordRepository();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_record()
    {
        $this->assertEmpty($this->recordRepository->all());
    }

    /**
     * @test
     */
    public function it_adds_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::fromString('record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, LabelCollection::fromArray([]));

        $this->recordRepository->add($record);

        $enrichedEntityFound = $this->recordRepository->findOneByIdentifier($identifier);
        $this->assertTrue($record->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_identifier_is_not_found()
    {
        $enrichedEntity = $this->recordRepository->findOneByIdentifier(RecordIdentifier::fromString('unknown_identifier'));
        $this->assertNull($enrichedEntity);
    }

    /**
     * @test
     */
    public function it_returns_all_the_records()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');

        $identifier1 = RecordIdentifier::fromString('identifier1');
        $record1 = Record::create($identifier1, $enrichedEntityIdentifier, LabelCollection::fromArray([]));
        $identifier2 = RecordIdentifier::fromString('identifier2');
        $record2 = Record::create($identifier2, $enrichedEntityIdentifier, LabelCollection::fromArray([]));

        $this->recordRepository->add($record1);
        $this->recordRepository->add($record2);
        $recordFound = $this->recordRepository->all();

        $this->assertRecordList([$record1, $record2], $recordFound);
    }

    /**
     * @param Record[] $records
     * @param Record[] $recordsFound
     */
    private function assertRecordList(array $records, array $recordsFound): void
    {
        foreach ($records as $enrichedEntity) {
            $isFound = false;
            foreach ($recordsFound as $enrichedEntityFound) {
                if (!$isFound && $enrichedEntityFound->equals($enrichedEntity)) {
                    $isFound = true;
                }

            }
            $this->assertTrue(
                $isFound,
                sprintf('The record with identifier %s was not found', (string) $enrichedEntity->getIdentifier())
            );
        }
    }
}

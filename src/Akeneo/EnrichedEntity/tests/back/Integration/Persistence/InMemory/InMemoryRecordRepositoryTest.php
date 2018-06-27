<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Tests\Back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepository;
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
    public function it_save_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::fromString('record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, []);

        $this->recordRepository->save($record);

        $enrichedEntityFound = $this->recordRepository->getByIdentifier($identifier);
        $this->assertTrue($record->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_identifier_is_not_found()
    {
        $enrichedEntity = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString('unknown_identifier'));
        $this->assertNull($enrichedEntity);
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

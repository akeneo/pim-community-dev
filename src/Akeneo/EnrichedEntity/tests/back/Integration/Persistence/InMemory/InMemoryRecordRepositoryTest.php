<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\InMemoryRecordRepository;
use PHPUnit\Framework\TestCase;

class InMemoryRecordRepositoryTest extends TestCase
{
    /** @var RecordRepositoryInterface */
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
        $identifier = RecordIdentifier::fromString('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);

        $this->recordRepository->save($record);

        $recordFound = $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
        $this->assertTrue($record->equals($recordFound));
    }

    /**
     * @test
     */
    public function it_counts_the_records()
    {
        $this->assertEquals(0, $this->recordRepository->count());

        $identifier = RecordIdentifier::fromString('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);

        $this->recordRepository->save($record);

        $this->assertEquals(1, $this->recordRepository->count());

        $identifier = RecordIdentifier::fromString('enriched_entity_identifier', 'record_identifier2');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier2'), []);

        $this->recordRepository->save($record);

        $this->assertEquals(2, $this->recordRepository->count());
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EntityNotFoundException::class);
        $identifier = RecordIdentifier::fromString('enriched_entity_identifier', 'unknown_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');

        $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_enriched_entity_identifier_is_not_found()
    {
        $this->expectException(EntityNotFoundException::class);
        $identifier = RecordIdentifier::fromString('unknown_enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('unknown_enriched_entity_identifier');

        $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
    }
}

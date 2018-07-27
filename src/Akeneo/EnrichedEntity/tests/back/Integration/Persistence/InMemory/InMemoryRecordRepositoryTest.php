<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\Fake\InMemoryRecordRepository;
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
    public function it_creates_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);

        $this->recordRepository->create($record);

        $recordFound = $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
        $this->assertTrue($record->equals($recordFound));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_existing_record_with_same_identifier()
    {
        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);
        $this->recordRepository->create($record);

        $this->expectException(\RuntimeException::class);
        $this->recordRepository->create($record);
    }

    /**
     * @test
     */
    public function it_updates_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);
        $this->recordRepository->create($record);
        $record->updateLabels(LabelCollection::fromArray(['fr_FR' => 'stylist']));

        $this->recordRepository->update($record);
        $recordFound = $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);

        $this->assertTrue($record->equals($recordFound));
    }

    /**
     * @test
     */
    public function it_throws_when_updating_a_non_existing_record()
    {
        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);

        $this->expectException(\RuntimeException::class);
        $this->recordRepository->update($record);
    }

    /**
     * @test
     */
    public function it_counts_the_records()
    {
        $this->assertEquals(0, $this->recordRepository->count());

        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);

        $this->recordRepository->create($record);

        $this->assertEquals(1, $this->recordRepository->count());

        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier2');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier2'), []);

        $this->recordRepository->create($record);

        $this->assertEquals(2, $this->recordRepository->count());
    }

    public function it_tells_if_it_has_a_record_identifier()
    {
        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, RecordCode::fromString('record_identifier'), []);

        $this->recordRepository->create($record);
        $this->assertTrue($this->recordRepository->hasRecord($identifier));
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);
        $identifier = RecordIdentifier::create('enriched_entity_identifier', 'unknown_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');

        $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_enriched_entity_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);
        $identifier = RecordIdentifier::create('unknown_enriched_entity_identifier', 'record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('unknown_enriched_entity_identifier');

        $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
    }
}

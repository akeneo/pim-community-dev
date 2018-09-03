<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
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
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordCode, []);

        $this->recordRepository->create($record);

        $recordFound = $this->recordRepository->getByIdentifier($identifier);
        $this->assertTrue($record->equals($recordFound));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_existing_record_with_same_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordCode, []);
        $this->recordRepository->create($record);

        $this->expectException(\RuntimeException::class);
        $this->recordRepository->create($record);
    }

    /**
     * @test
     */
    public function it_updates_a_record_and_returns_it()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordCode, []);
        $this->recordRepository->create($record);
        $record->setLabels(LabelCollection::fromArray(['fr_FR' => 'stylist']));

        $this->recordRepository->update($record);
        $recordFound = $this->recordRepository->getByIdentifier($identifier);

        $this->assertTrue($record->equals($recordFound));
    }

    /**
     * @test
     */
    public function it_throws_when_updating_a_non_existing_record()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordCode, []);

        $this->expectException(\RuntimeException::class);
        $this->recordRepository->update($record);
    }

    /**
     * @test
     */
    public function it_counts_the_records()
    {
        $this->assertEquals(0, $this->recordRepository->count());

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordCode, []);

        $this->recordRepository->create($record);

        $this->assertEquals(1, $this->recordRepository->count());

        $recordIdentifier = RecordCode::fromString('record_identifier');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordIdentifier);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordIdentifier, []);

        $this->recordRepository->create($record);

        $this->assertEquals(2, $this->recordRepository->count());
    }

    public function it_tells_if_it_has_a_record_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create($identifier, $enrichedEntityIdentifier, $recordCode, []);

        $this->recordRepository->create($record);
        $this->assertTrue($this->recordRepository->hasRecord($identifier));
    }

    /**
     * @test
     */
    public function it_throws_if_the_code_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('unknown_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);

        $this->recordRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_enriched_entity_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('unknown_enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);

        $this->recordRepository->getByIdentifier($identifier);
    }
}

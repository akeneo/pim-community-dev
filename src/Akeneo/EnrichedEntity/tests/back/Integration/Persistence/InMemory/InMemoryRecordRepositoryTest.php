<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\InMemoryRecordRepository;
use PHPUnit\Framework\Assert;
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
        $identifier = RecordIdentifier::fromString('record_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $record = Record::create($identifier, $enrichedEntityIdentifier, []);

        $this->recordRepository->save($record);

        $enrichedEntityFound = $this->recordRepository->getByIdentifier($enrichedEntityIdentifier, $identifier);
        $this->assertTrue($record->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_identifier_is_not_found()
    {
        $identifier = RecordIdentifier::fromString('unknown_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');

        $enrichedEntity = $this->recordRepository->getByIdentifier($enrichedEntityIdentifier, $identifier);
        Assert::assertNull($enrichedEntity);
        $this->recordRepository->getByIdentifier($identifier, $enrichedEntityIdentifier);
    }
}

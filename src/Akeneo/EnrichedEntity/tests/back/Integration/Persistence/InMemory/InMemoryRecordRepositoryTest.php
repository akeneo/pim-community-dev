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

namespace Akeneo\EnrichedEntity\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\TestCase;

class InMemoryRecordRepositoryTest extends TestCase
{
    /** @var InMemoryRecordRepository */
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

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::fromFileInfo($imageInfo),
            ValueCollection::fromValues([])
        );

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
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
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
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
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
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

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
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

        $this->recordRepository->create($record);

        $this->assertEquals(1, $this->recordRepository->count());

        $recordIdentifier = RecordCode::fromString('record_identifier');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordIdentifier);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordIdentifier,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

        $this->recordRepository->create($record);

        $this->assertEquals(2, $this->recordRepository->count());
    }

    public function it_tells_if_it_has_a_record_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

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

    /**
     * @test
     */
    public function it_deletes_a_record_by_code_and_entity_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $this->recordRepository->deleteByEnrichedEntityAndCode($enrichedEntityIdentifier, $recordCode);

        $this->expectException(RecordNotFoundException::class);
        $this->recordRepository->deleteByEnrichedEntityAndCode($enrichedEntityIdentifier, $recordCode);
    }

    /**
     * @test
     */
    public function it_throws_if_trying_to_delete_an_unknown_record()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $unknownCode = RecordCode::fromString('unknown_code');

        $this->expectException(RecordNotFoundException::class);
        $this->recordRepository->deleteByEnrichedEntityAndCode($enrichedEntityIdentifier, $unknownCode);
    }
}

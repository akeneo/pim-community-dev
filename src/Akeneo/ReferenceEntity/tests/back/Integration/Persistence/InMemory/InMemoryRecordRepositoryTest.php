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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;
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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
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
    public function it_throws_when_creating_an_existing_record_with_same_entity_identifier_and_same_code()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

        $this->expectException(\RuntimeException::class);
        $this->recordRepository->create($record);
    }

    /**
     * @test
     */
    public function it_updates_a_record_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
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

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

        $this->recordRepository->create($record);

        $this->assertEquals(1, $this->recordRepository->count());

        $recordIdentifier = RecordCode::fromString('record_identifier');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordIdentifier);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('unknown_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $this->recordRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_reference_entity_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('unknown_reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $this->recordRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_a_record_by_code_and_entity_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $this->recordRepository->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);

        $hasRecord = 0 !== $this->recordRepository->count();
        Assert::assertFalse($hasRecord, 'Expected record to be removed, but was not');
    }

    /**
     * @test
     */
    public function it_throws_if_trying_to_delete_an_unknown_record()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $recordCode = RecordCode::fromString('record_code');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $unknownCode = RecordCode::fromString('unknown_code');

        $this->expectException(RecordNotFoundException::class);
        $this->recordRepository->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $unknownCode);
    }
}

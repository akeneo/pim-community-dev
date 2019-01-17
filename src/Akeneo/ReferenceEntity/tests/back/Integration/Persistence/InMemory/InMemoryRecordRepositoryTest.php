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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
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
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($imageInfo)
                ),
            ])
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
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
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
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

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
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $unknownCode = RecordCode::fromString('unknown_code');

        $this->expectException(RecordNotFoundException::class);
        $this->recordRepository->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $unknownCode);
    }

    /**
     * @test
     */
    public function it_counts_the_records_by_reference_entity()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $this->assertSame(0, $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier));

        $starck = Record::create(
            RecordIdentifier::fromString('starck_designer'),
            $referenceEntityIdentifier,
            RecordCode::fromString('starck'),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('label_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Philippe Starck')
                ),
            ])
        );
        $this->recordRepository->create($starck);
        $this->assertSame(1, $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier));

        $bob = Record::create(
            RecordIdentifier::fromString('bob_designer'),
            $referenceEntityIdentifier,
            RecordCode::fromString('bob'),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('label_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Bob')
                ),
            ])
        );
        $this->recordRepository->create($bob);
        $this->assertSame(2, $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier));
    }
}

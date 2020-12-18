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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use AkeneoEnterprise\Test\IntegrationTestsBundle\EventDispatcher\EventDispatcherMock;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;

class SqlRecordRepositoryTest extends SqlIntegrationTestCase
{
    /** @var EventDispatcherMock */
    private $eventDispatcherMock;

    /** @var RecordRepositoryInterface */
    private $repository;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var array */
    private $fixturesDesigner;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $this->repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->eventDispatcherMock = $this->get('event_dispatcher');
        $this->eventDispatcherMock->reset();

        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_creates_a_record_with_no_values_and_returns_it()
    {
        $recordCode = RecordCode::fromString('starck');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        );

        $this->repository->create($record);

        $this->eventDispatcherMock->assertEventDispatched(RecordUpdatedEvent::class);
        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertSame($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_existing_record_with_same_entity_identifier_and_same_code()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );

        $this->repository->create($record);
        $this->eventDispatcherMock->reset();

        $recordCode = RecordCode::fromString('starck');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );

        $this->expectException(DBALException::class);
        $this->repository->create($record);
        $this->eventDispatcherMock->assertNoEventDispatched();
    }

    /**
     * @test
     */
    public function it_creates_a_record_with_no_values_and_finds_it_by_reference_entity_and_record_code()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );

        $this->repository->create($record);

        $recordFound = $this->repository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        $this->assertEquals($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_creates_a_record_with_values_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg')
            ->setSize(1024)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

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
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsImageReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($imageInfo)
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['name']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['main_image']->getIdentifier(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo)
                )
            ])
        );

        $this->repository->create($record);

        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($record->normalize(), $recordFound->normalize());

        $recordFound = $this->repository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        $this->assertEquals($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_throws_when_creating_a_record_with_the_same_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );
        $this->repository->create($record);

        $this->expectException(DBALException::class);
        $this->repository->create($record);
    }

    /**
     * @test
     */
    public function it_updates_a_record_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg');

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['name']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('An old description')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['main_image']->getIdentifier(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo)
                )
            ])
        );
        $this->repository->create($record);
        $this->eventDispatcherMock->reset();

        $valueToUpdate = Value::create(
            $this->fixturesDesigner['attributes']['name']->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('A completely new and updated description')
        );
        $record->setValue($valueToUpdate);
        $this->repository->update($record);

        $this->eventDispatcherMock->assertEventDispatched(RecordUpdatedEvent::class);
        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_replaces_record_value_codes_by_their_identifiers_when_creating_a_record()
    {
        // Create the brand we will link
        $referenceEntityIdentifierBrand = ReferenceEntityIdentifier::fromString('brand');
        $brandCode = RecordCode::fromString('ikea');
        $brandIdentifier = $this->repository->nextIdentifier($referenceEntityIdentifierBrand, $brandCode);
        $record = Record::create(
            $brandIdentifier,
            $referenceEntityIdentifierBrand,
            $brandCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($record);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['brand']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    RecordData::createFromNormalize('ikea')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['brands']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    RecordCollectionData::createFromNormalize(['ikea'])
                ),
            ])
        );
        $this->repository->create($record);
        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_replaces_record_value_codes_by_their_identifiers_when_updating_a_record()
    {
        // Create the brand we will link
        $referenceEntityIdentifierBrand = ReferenceEntityIdentifier::fromString('brand');
        $brandCode = RecordCode::fromString('ikea');
        $brandIdentifier = $this->repository->nextIdentifier($referenceEntityIdentifierBrand, $brandCode);
        $record = Record::create(
            $brandIdentifier,
            $referenceEntityIdentifierBrand,
            $brandCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($record);

        // Create the designer we will update
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );
        $this->repository->create($record);
        $this->eventDispatcherMock->reset();

        $valueToUpdate = Value::create(
            $this->fixturesDesigner['attributes']['brand']->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            RecordData::createFromNormalize('ikea')
        );
        $record->setValue($valueToUpdate);
        $valueToUpdate = Value::create(
            $this->fixturesDesigner['attributes']['brands']->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            RecordCollectionData::createFromNormalize(['ikea'])
        );
        $record->setValue($valueToUpdate);
        $this->repository->update($record);

        $this->eventDispatcherMock->assertEventDispatched(RecordUpdatedEvent::class);
        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_counts_the_records()
    {
        $this->assertEquals(0, $this->repository->count());
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $recordCode = RecordCode::fromString('record_identifier');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        );

        $this->repository->create($record);

        $this->assertEquals(1, $this->repository->count());

        $recordCode = RecordCode::fromString('record_identifier2');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        );

        $this->repository->create($record);

        $this->assertEquals(2, $this->repository->count());
    }

    public function it_retrieve_the_next_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $nextIdentifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $this->assertNotEmpty($nextIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('unknown_identifier');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $this->repository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_records_by_reference_entity_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');

        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($record);

        $recordCode = RecordCode::fromString('dyson');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($record);

        $referenceEntityIdentifierBrand = ReferenceEntityIdentifier::fromString('brand');
        $recordCode = RecordCode::fromString('bar');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifierBrand, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifierBrand,
            $recordCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($record);

        Assert::assertEquals(3, $this->repository->count());

        $this->repository->deleteByReferenceEntity($referenceEntityIdentifier);
        $this->eventDispatcherMock->assertEventDispatched(ReferenceEntityRecordsDeletedEvent::class);
        Assert::assertEquals(1, $this->repository->count());
    }

    /**
     * @test
     */
    public function it_deletes_a_record_by_code_and_entity_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($record);

        $this->repository->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);

        $this->eventDispatcherMock->assertEventDispatched(RecordDeletedEvent::class);
        $this->expectException(RecordNotFoundException::class);
        $this->repository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
    }

    /**
     * @test
     */
    public function it_throws_if_trying_to_delete_an_unknown_record()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $unknownCode = RecordCode::fromString('unknown_code');

        $this->expectException(RecordNotFoundException::class);
        $this->repository->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $unknownCode);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->referenceEntity('brand')
            ->load();

        $this->fixturesDesigner = $this->fixturesLoader
            ->referenceEntity('designer')
            ->withAttributes(['name', 'main_image', 'brand', 'brands', 'website'])
            ->load();
    }

    /**
     * @test
     */
    public function it_counts_the_records_by_reference_entity()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $this->assertSame(0, $this->repository->countByReferenceEntity($referenceEntityIdentifier));

        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $starck = Record::create(
            $this->repository->nextIdentifier($referenceEntityIdentifier, RecordCode::fromString('starck')),
            $referenceEntityIdentifier,
            RecordCode::fromString('starck'),
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Philippe Starck')
                ),
            ])
        );
        $this->repository->create($starck);
        $this->assertSame(1, $this->repository->countByReferenceEntity($referenceEntityIdentifier));

        $bob = Record::create(
            $this->repository->nextIdentifier($referenceEntityIdentifier, RecordCode::fromString('bob')),
            $referenceEntityIdentifier,
            RecordCode::fromString('bob'),
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Bob')
                ),
            ])
        );
        $this->repository->create($bob);
        $this->assertSame(2, $this->repository->countByReferenceEntity($referenceEntityIdentifier));
    }
}

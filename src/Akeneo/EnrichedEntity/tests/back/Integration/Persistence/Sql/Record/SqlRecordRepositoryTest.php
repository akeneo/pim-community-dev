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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\FileData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\DBALException;

class SqlRecordRepositoryTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.record');
        $this->resetDB();
        $this->loadEnrichedEntityWithAttributes();
    }

    /**
     * @test
     */
    public function it_creates_a_record_with_no_values_and_returns_it()
    {
        $recordCode = RecordCode::fromString('starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck'],
            ValueCollection::fromValues([])
        );

        $this->repository->create($record);

        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertSame($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_creates_a_record_with_values_and_returns_it()
    {
        $recordCode = RecordCode::fromString('starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image.png')
            ->setKey('/a/file/image');

        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck'],
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo)
                )
            ])
        );

        $this->repository->create($record);

        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertSame($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_throws_when_creating_a_record_with_the_same_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck'],
            ValueCollection::fromValues([])
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
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image.png')
            ->setKey('/a/file/image');
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck'],
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('An old description')
                ),
                Value::create(
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo)
                )
            ])
        );
        $this->repository->create($record);
        $record->setLabels(LabelCollection::fromArray(['fr_FR' => 'Coco']));
        $valueToUpdate = Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('An completely new and updated description')
        );
        $valueToAdd = Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Une valeur de test qui n\'éxistait pas avant')
        );
        $record->setValue($valueToUpdate);
        $record->setValue($valueToAdd);

        $this->repository->update($record);
        $recordFound = $this->repository->getByIdentifier($identifier);

        $this->assertSame($record->normalize(), $recordFound->normalize());
    }

    /**
     * @test
     */
    public function it_throws_when_updating_a_non_existing_record()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck'],
            ValueCollection::fromValues([])
        );

        $this->expectException(\RuntimeException::class);
        $this->repository->update($record);
    }

    /**
     * @test
     */
    public function it_counts_the_records()
    {
        $this->assertEquals(0, $this->repository->count());

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $recordCode = RecordCode::fromString('record_identifier');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            ValueCollection::fromValues([])
        );

        $this->repository->create($record);

        $this->assertEquals(1, $this->repository->count());

        $recordCode = RecordCode::fromString('record_identifier2');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            $recordCode,
            [],
            ValueCollection::fromValues([])
        );

        $this->repository->create($record);

        $this->assertEquals(2, $this->repository->count());
    }

    public function it_retrieve_the_next_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $nextIdentifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);

        $this->assertNotEmpty($nextIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('unknown_identifier');
        $identifier = $this->repository->nextIdentifier($enrichedEntityIdentifier, $recordCode);

        $this->repository->getByIdentifier($identifier);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityWithAttributes(): void
    {
        $repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ],
            null
        );
        $repository->create($enrichedEntity);

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $image = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'fingerprint'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $attributesRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
    }
}

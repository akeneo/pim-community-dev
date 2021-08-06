<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * Testing the search usecases for the record grid for information in the code of the record.
 *
 * @see       https://akeneo.atlassian.net/wiki/spaces/AKN/pages/572424236/Search+an+entity+record
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexerTest extends SearchIntegrationTestCase
{
    /** @var RecordIndexerInterface */
    protected $recordIndexer;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->recordIndexer = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer');
    }

    /**
     * @test
     */
    public function it_indexes_one_record()
    {
        $this->searchRecordIndexHelper->resetIndex();
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');

        $this->recordIndexer->index(RecordIdentifier::fromString('stark_designer_fingerprint'));

        $this->searchRecordIndexHelper->assertRecordExists('designer', 'stark');
    }

    /**
     * @test
     */
    public function it_indexes_multiple_records_by_identifiers()
    {
        $this->searchRecordIndexHelper->resetIndex();
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'coco');

        $this->recordIndexer->indexByRecordIdentifiers([
            RecordIdentifier::fromString('stark_designer_fingerprint'),
            RecordIdentifier::fromString('coco_designer_fingerprint')
        ]);

        $this->searchRecordIndexHelper->assertRecordExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordExists('designer', 'coco');
    }

    /**
     * @test
     */
    public function it_indexes_by_reference_entity()
    {
        $this->searchRecordIndexHelper->resetIndex();
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'coco');

        $this->recordIndexer->indexByReferenceEntity(ReferenceEntityIdentifier::fromString('designer'));

        $this->searchRecordIndexHelper->assertRecordExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordExists('designer', 'coco');
    }

    /**
     * @test
     */
    public function it_deletes_one_record()
    {
        $this->searchRecordIndexHelper->refreshIndex();
        $this->recordIndexer->removeRecordByReferenceEntityIdentifierAndCode('designer', 'stark');

        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordExists('designer', 'coco');
        Assert::assertCount(1, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
        Assert::assertCount(1, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('another_reference_entity'));
    }

    /**
     * @test
     */
    public function it_deletes_all_reference_entity_records()
    {
        $this->recordIndexer->removeByReferenceEntityIdentifier('designer');

        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'coco');
        Assert::assertCount(0, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
        Assert::assertCount(1, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('another_reference_entity'));
    }

    /**
     * @test
     */
    public function it_refreshes_the_index()
    {
        $isExceptionThrown = false;
        try {
            $this->recordIndexer->refresh();
        } catch (\Exception $e) {
            $isExceptionThrown = true;
        }
        Assert::assertFalse($isExceptionThrown, 'An unexpected exception has been thrown');
    }

    private function loadFixtures()
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntities();
        $this->loadAttributes();
        $this->loadRecords();
        $this->searchRecordIndexHelper->refreshIndex();
    }

    private function loadReferenceEntities(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString('designer'),
                [],
                Image::createEmpty()
            )
        );

        $referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString('another_reference_entity'),
                [],
                Image::createEmpty()
            )
        );
    }

    private function loadAttributes(): void
    {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('name'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        $attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create('designer', 'image', 'fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                AttributeCode::fromString('portrait'),
                LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('200.10'),
                AttributeAllowedExtensions::fromList(['gif'])
            )
        );

        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('another_reference_entity', 'name', 'fingerprint'), ReferenceEntityIdentifier::fromString('another_reference_entity'),
                AttributeCode::fromString('name'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    private function loadRecords(): void
    {
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString('stark_designer_fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                    Value::create(
                        AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Philippe stark')
                    ),
                    Value::create(
                        AttributeIdentifier::create('designer', 'image', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromNormalize([
                                'filePath'         => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                                'originalFilename' => 'file.gif',
                                'size'             => 1024,
                                'mimeType'         => 'image/gif',
                                'extension'        => 'gif',
                            ]
                        )
                    ),
                ])
            )
        );

        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString('coco_designer_fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('coco'),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Coco')
                    ),
                    Value::create(
                        AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Coco Chanel')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('image'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromNormalize([
                                'filePath'         => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                                'originalFilename' => 'coco.gif',
                                'size'             => 1024,
                                'mimeType'         => 'image/gif',
                                'extension'        => 'gif',
                            ]
                        )
                    ),
                ])
            )
        );

        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString('another_record_another_reference_entity'),
                ReferenceEntityIdentifier::fromString('another_reference_entity'),
                RecordCode::fromString('another_record'),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Coco')
                    ),
                    Value::create(
                        AttributeIdentifier::create('another_reference_entity', 'name', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Another name')
                    ),
                ])
            )
        );
    }
}

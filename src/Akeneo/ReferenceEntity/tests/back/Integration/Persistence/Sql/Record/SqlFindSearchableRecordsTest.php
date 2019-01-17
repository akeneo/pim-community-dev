<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlFindSearchableRecords;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindSearchableRecordsTest extends SqlIntegrationTestCase
{
    /** @var SqlFindSearchableRecords */
    private $findSearchableRecords;

    public function setUp()
    {
        parent::setUp();

        $this->findSearchableRecords = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.find_searchable_records');
        $this->loadReferenceEntityAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_null_if_it_does_not_find_by_record_identifier()
    {
        Assert::assertNull(
            $this->findSearchableRecords->byRecordIdentifier(RecordIdentifier::fromString('wrong_identifier'))
        );
    }

    /**
     * @test
     */
    public function it_returns_a_searchable_record_item()
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        /** @var ReferenceEntity $referenceEntity */
        $referenceEntity = $referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));

        $labelIdentifier = $referenceEntity->getAttributeAsLabelReference()->normalize();
        $searchableRecord = $this->findSearchableRecords->byRecordIdentifier(RecordIdentifier::fromString('stark_designer_fingerprint'));
        Assert::assertEquals('stark_designer_fingerprint', $searchableRecord->identifier);
        Assert::assertEquals('stark', $searchableRecord->code);
        Assert::assertEquals('designer', $searchableRecord->referenceEntityIdentifier);
        Assert::assertSame(['fr_FR' => 'Philippe Starck'], $searchableRecord->labels);
        Assert::assertSame([
            'name'                      => [
                'data' => 'Philippe stark',
                'locale' => null,
                'channel' => null,
                'attribute' => 'name',
            ],
            $labelIdentifier . '_fr_FR' => [
                'data'      => 'Philippe Starck',
                'locale'    => 'fr_FR',
                'channel'   => null,
                'attribute' => $labelIdentifier,
            ],
        ], $searchableRecord->values);
    }

    /**
     * @test
     */
    public function it_returns_null_if_it_does_not_find_by_reference_entity_identifier()
    {
        $items = $this->findSearchableRecords->byReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString('wrong_reference_entity')
        );
        $count = 0;
        foreach ($items as $searchItem) {
            $count++;
        }
        Assert::assertEquals(0, $count, 'There was some searchable item found. expected 0.');
    }

    /**
     * @test
     */
    public function it_returns_searchable_record_items_by_reference_entity()
    {
        $searchableRecords = $this->findSearchableRecords->byReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString('wrong_reference_entity')
        );
        foreach ($searchableRecords as $searchableRecord) {
            Assert::assertEquals('stark_designer_fingerprint', $searchableRecord->identifier);
            Assert::assertEquals('stark', $searchableRecord->code);
            Assert::assertEquals('designer', $searchableRecord->referenceEntityIdentifier);
            Assert::assertSame(['fr_FR' => 'Philippe Starck'], $searchableRecord->labels);
            Assert::assertSame([
                'name' => [
                    'data' => 'Philippe stark',
                    'locale' => null,
                    'channel' => null,
                    'attribute' => 'name',
                ]
            ], $searchableRecord->values);
        }
    }

    private function loadReferenceEntityAndAttributes(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
        $referenceEntity = $referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString('stark_designer_fingerprint'),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString('stark'),
                ValueCollection::fromValues([
                    Value::create(
                        $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Philippe stark')
                    )
                ])
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindEnrichedEntityItemsInterface;
use Akeneo\EnrichedEntity\Domain\Query\RecordItem;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindEnrichedEntityItemsTest extends SqlIntegrationTestCase
{
    /** @var FindEnrichedEntityItemsInterface */
    private $findEnrichedEntityItems;

    public function setUp()
    {
        parent::setUp();

        $this->findEnrichedEntityItems = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_items_for_enriched_entity');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_records_corresponding_to_the_identifier()
    {
        $this->assertEmpty(($this->findEnrichedEntityItems)(EnrichedEntityIdentifier::fromString('unknown_enriched_entity')));
    }

    /**
     * @test
     */
    public function it_returns_all_the_records_for_an_enriched_entity()
    {
        $recordItems = ($this->findEnrichedEntityItems)(EnrichedEntityIdentifier::fromString('designer'));

        $starck = new RecordItem();
        $starck->identifier = RecordIdentifier::create('designer', 'starck');
        $starck->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $starck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $coco = new RecordItem();
        $coco->identifier = RecordIdentifier::create('designer', 'coco');
        $coco->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $coco->labels = LabelCollection::fromArray(['fr_FR' => 'Coco Chanel']);

        $this->assertRecordItem($starck, $recordItems[0]);
        $this->assertRecordItem($coco, $recordItems[1]);
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_record;
            DELETE FROM akeneo_enriched_entity_enriched_entity;
SQL;

        $this->get('database_connection')->executeQuery($resetQuery);
    }

    private function loadEnrichedEntityAndRecords(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);

        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::create('designer', 'starck'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('starck'),
                ['fr_Fr' => 'Philippe Starck']
            )
        );
        $recordRepository->create(
            Record::create(
                RecordIdentifier::create('designer', 'coco'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('coco'),
                ['fr_Fr' => 'Coco Chanel']
            )
        );
    }

    private function assertRecordItem(RecordItem $expected, RecordItem $actual): void
    {
        $this->assertTrue($expected->identifier->equals($actual->identifier), 'Record identifiers are not equal');
        $this->assertEquals(
            $expected->enrichedEntityIdentifier,
            $actual->enrichedEntityIdentifier,
            'Enriched entity identifier are not the same'
        );
        $expectedLabels = $expected->labels->normalize();
        $actualLabels = $actual->labels->normalize();
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            ),
            'Labels for the record item are not the same'
        );
    }
}

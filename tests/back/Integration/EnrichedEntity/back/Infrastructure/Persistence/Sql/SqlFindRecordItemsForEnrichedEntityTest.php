<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\Sql;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Query\FindRecordItemsForEnrichedEntityQuery;
use Akeneo\EnrichedEntity\back\Domain\Query\RecordItem;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlFindRecordItemsForEnrichedEntityTest extends TestCase
{
    /** @var FindRecordItemsForEnrichedEntityQuery */
    private $findRecordsForEnrichedEntity;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $this->findRecordsForEnrichedEntity = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_items_for_enriched_entity');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_records_corresponding_to_the_identifier()
    {
        $this->assertEmpty(($this->findRecordsForEnrichedEntity)(EnrichedEntityIdentifier::fromString('unknown_enriched_entity')));
    }

    /**
     * @test
     */
    public function it_returns_all_the_records_for_an_enriched_entity()
    {
        $recordItems = ($this->findRecordsForEnrichedEntity)(EnrichedEntityIdentifier::fromString('designer'));

        $starck = new RecordItem();
        $starck->identifier = 'starck';
        $starck->enrichedEntityIdentifier = 'designer';
        $starck->labels = ['fr_FR' => 'Philippe Starck'];

        $coco = new RecordItem();
        $coco->identifier = 'coco';
        $coco->enrichedEntityIdentifier = 'designer';
        $coco->labels = ['fr_FR' => 'Coco Chanel'];

        $this->assertRecordItem($starck, $recordItems[0]);
        $this->assertRecordItem($coco, $recordItems[1]);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return null;
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_enriched_entity;
            DELETE FROM akeneo_enriched_entity_record;
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
        $enrichedEntityRepository->save($enrichedEntity);

        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->save(
            Record::create(
                RecordIdentifier::fromString('starck'),
                EnrichedEntityIdentifier::fromString('designer'),
                ['fr_Fr' => 'Philippe Starck']
            )
        );
        $recordRepository->save(
            Record::create(
                RecordIdentifier::fromString('coco'),
                EnrichedEntityIdentifier::fromString('designer'),
                ['fr_Fr' => 'Coco Chanel']
            )
        );
    }

    private function assertRecordItem(RecordItem $expected, RecordItem $actual)
    {
        $this->assertEquals($expected->identifier, $actual->identifier);
        $this->assertEquals($expected->enrichedEntityIdentifier, $actual->enrichedEntityIdentifier);
        $this->assertEmpty(
            array_merge(
                array_diff($expected->labels, $actual->labels),
                array_diff($actual->labels, $expected->labels)
            )
        );
    }
}

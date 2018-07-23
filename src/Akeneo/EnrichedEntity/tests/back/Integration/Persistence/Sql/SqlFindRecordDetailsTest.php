<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\RecordDetails;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindRecordDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetailsQuery;

    public function setUp()
    {
        parent::setUp();

        $this->findRecordDetailsQuery = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_records()
    {
        $this->assertNull(($this->findRecordDetailsQuery)(
                RecordIdentifier::fromString('unknown_enriched_entity', 'unknown_record_identifier'),
                EnrichedEntityIdentifier::fromString('unknown_enriched_entity')
            )
        );
    }

    /**
     * @test
     */
    public function it_returns_the_record_details()
    {
        $actualStarck = ($this->findRecordDetailsQuery)(
            RecordIdentifier::fromString('designer', 'starck'),
            EnrichedEntityIdentifier::fromString('designer')
        );

        $expectedStarck = new RecordDetails();
        $expectedStarck->identifier = RecordIdentifier::fromString('designer', 'starck');
        $expectedStarck->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedStarck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $this->assertRecordDetails($expectedStarck, $actualStarck);
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
                RecordIdentifier::fromString('designer', 'starck'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('starck'),
                ['fr_Fr' => 'Philippe Starck']
            )
        );
        $recordRepository->save(
            Record::create(
                RecordIdentifier::fromString('designer', 'coco'),
                EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('coco'),
                ['fr_Fr' => 'Coco Chanel']
            )
        );
    }

    private function assertRecordDetails(RecordDetails $expected, RecordDetails $actual)
    {
        $this->assertEquals($expected->identifier, $actual->identifier);
        $this->assertEquals($expected->enrichedEntityIdentifier, $actual->enrichedEntityIdentifier);
        $expectedLabels = $this->normalizeLabels($expected->labels);
        $actualLabels = $this->normalizeLabels($actual->labels);
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            )
        );
    }

    private function normalizeLabels(LabelCollection $labelCollection): array
    {
        $labels = [];
        foreach ($labelCollection->getLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $labelCollection->getLabel($localeCode);
        }

        return $labels;
    }
}

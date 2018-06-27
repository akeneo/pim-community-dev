<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Tests\Back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\RecordDetails;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlFindRecordDetailsTest extends TestCase
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
                EnrichedEntityIdentifier::fromString('unknown_enriched_entity'),
                RecordIdentifier::fromString('unknown_record_identifier')
            )
        );
    }

    /**
     * @test
     */
    public function it_returns_the_record_details()
    {
        $actualStarck = ($this->findRecordDetailsQuery)(
            EnrichedEntityIdentifier::fromString('designer'),
            RecordIdentifier::fromString('starck')
        );

        $expectedStarck = new RecordDetails();
        $expectedStarck->identifier = RecordIdentifier::fromString('starck');
        $expectedStarck->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedStarck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $this->assertRecordDetails($expectedStarck, $actualStarck);
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

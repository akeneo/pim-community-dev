<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\ExistsRecordInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlExistsRecordTest extends SqlIntegrationTestCase
{
    /** @var ExistsRecordInterface */
    private $existsRecord;

    public function setUp()
    {
        parent::setUp();

        $this->existsRecord = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.exists_record');
        $this->resetDB();
        $this->loadEnrichedEntityDesigner();
        $this->loadRecordStarck();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_identifier()
    {
        $this->assertTrue($this->existsRecord->withIdentifier(RecordIdentifier::create('designer', 'starck')));
        $this->assertFalse($this->existsRecord->withIdentifier(RecordIdentifier::create('designer', 'Coco')));
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityDesigner(): void
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
    }

    public function loadRecordStarck(): void
    {
        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::create('designer', 'starck'), EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('starck'), ['fr_FR' => 'Philippe Starck']
            )
        );
    }
}

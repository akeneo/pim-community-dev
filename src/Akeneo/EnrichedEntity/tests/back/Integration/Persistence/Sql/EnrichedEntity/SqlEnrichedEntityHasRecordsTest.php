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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityHasRecordsInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlEnrichedEntityHasRecordsTest extends SqlIntegrationTestCase
{
    /** @var EnrichedEntityHasRecordsInterface */
    private $enrichedEntityHasRecords;

    public function setUp()
    {
        parent::setUp();

        $this->enrichedEntityHasRecords = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.enriched_entity_has_records');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_tells_if_an_enriched_entity_has_records()
    {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $hasRecords = ($this->enrichedEntityHasRecords)($identifier);
        $this->assertTrue($hasRecords);

        $identifier = EnrichedEntityIdentifier::fromString('brand');
        $hasRecords = ($this->enrichedEntityHasRecords)($identifier);
        $this->assertFalse($hasRecords);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityAndRecords(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('stark');
        $enrichedEntity = EnrichedEntity::create(
            $enrichedEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);

        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);

        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->create(
            Record::create(
                $recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode),
                $enrichedEntityIdentifier,
                $recordCode,
                ['fr_FR' => 'Philippe Starck'],
                ValueCollection::fromValues([])
            )
        );
    }
}

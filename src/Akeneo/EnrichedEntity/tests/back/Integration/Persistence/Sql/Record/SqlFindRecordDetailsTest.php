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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindRecordDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetailsQuery;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var RecordIdentifier */
    private $recordIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->findRecordDetailsQuery = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $this->recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_records()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('unknown_enriched_entity');
        $recordCode = RecordCode::fromString('unknown_record_identifier');
        $recordIdentifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);
        $this->assertNull(($this->findRecordDetailsQuery)($recordIdentifier));
    }

    /**
     * @test
     */
    public function it_returns_the_record_details()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $actualStarck = ($this->findRecordDetailsQuery)($this->recordIdentifier);

        $expectedStarck = new RecordDetails();
        $expectedStarck->identifier = $this->recordIdentifier;
        $expectedStarck->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $expectedStarck->code = $recordCode;
        $expectedStarck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $this->assertRecordDetails($expectedStarck, $actualStarck);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityAndRecords(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $enrichedEntity = EnrichedEntity::create(
            $enrichedEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            null
        );
        $enrichedEntityRepository->create($enrichedEntity);

        $starckCode = RecordCode::fromString('starck');
        $this->recordIdentifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $starckCode);
        $this->recordRepository->create(
            Record::create(
                $this->recordIdentifier,
                $enrichedEntityIdentifier,
                $starckCode,
                ['fr_Fr' => 'Philippe Starck']
            )
        );
    }

    private function assertRecordDetails(RecordDetails $expected, RecordDetails $actual)
    {
        $this->assertEquals($expected->identifier, $actual->identifier);
        $this->assertEquals($expected->enrichedEntityIdentifier, $actual->enrichedEntityIdentifier);
        $expectedLabels = $expected->labels->normalize();
        $actualLabels = $actual->labels->normalize();
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            )
        );
    }
}

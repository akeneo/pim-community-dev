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
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordItemsForEnrichedEntityInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordItem;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindRecordItemsForEnrichedEntityTest extends SqlIntegrationTestCase
{
    /** @var FindRecordItemsForEnrichedEntityInterface */
    private $findRecordsForEnrichedEntity;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $cocoIdentifier;

    public function setUp()
    {
        parent::setUp();

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
        $starck->identifier = $this->starckIdentifier;
        $starck->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $starck->code = RecordCode::fromString('stark');
        $starck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $coco = new RecordItem();
        $coco->identifier = $this->cocoIdentifier;
        $coco->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $coco->code = RecordCode::fromString('coco');
        $coco->labels = LabelCollection::fromArray(['fr_FR' => 'Coco Chanel']);

        $this->assertRecordItem($starck, $recordItems[0]);
        $this->assertRecordItem($coco, $recordItems[1]);
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

        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $starckCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $recordRepository->nextIdentifier($enrichedEntityIdentifier, $starckCode);
        $recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $enrichedEntityIdentifier,
                $starckCode,
                ['fr_FR' => 'Philippe Starck']
            )
        );
        $cocoCode = RecordCode::fromString('coco');
        $this->cocoIdentifier = $recordRepository->nextIdentifier($enrichedEntityIdentifier, $cocoCode);
        $recordRepository->create(
            Record::create(
                $this->cocoIdentifier,
                $enrichedEntityIdentifier,
                $cocoCode,
                ['fr_FR' => 'Coco Chanel']
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

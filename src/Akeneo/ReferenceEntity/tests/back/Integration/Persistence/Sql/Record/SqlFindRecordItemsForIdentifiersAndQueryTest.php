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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindRecordItemsForIdentifiersAndQueryTest extends SqlIntegrationTestCase
{
    /** @var FindRecordItemsForIdentifiersAndQueryInterface */
    private $findRecordItemsForIdentifiersAndQuery;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $cocoIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->findRecordItemsForIdentifiersAndQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_items_for_identifiers_and_query');
        $this->resetDB();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_empty_collection_if_there_is_no_matching_identifiers()
    {
        $query = RecordQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $this->assertEmpty(($this->findRecordItemsForIdentifiersAndQuery)(['michel_sardou', 'bob_ross'], $query));
    }

    /**
     * @test
     */
    public function it_returns_record_items_for_matching_identifiers_with_same_order()
    {
        $query = RecordQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $recordItems = ($this->findRecordItemsForIdentifiersAndQuery)(
            [(string) $this->starckIdentifier, (string) $this->cocoIdentifier],
            $query
        );

        $starck = new RecordItem();
        $starck->identifier = (string) $this->starckIdentifier;
        $starck->referenceEntityIdentifier = 'designer';
        $starck->code = 'starck';
        $starck->labels = ['fr_FR' => 'Philippe Starck'];
        $starck->values = [];
        $starck->completenessPercentage = null;
        $starck->image = [
            'filePath' => null,
            'originalFilename' => null
        ];

        $coco = new RecordItem();
        $coco->identifier = (string) $this->cocoIdentifier;
        $coco->referenceEntityIdentifier = 'designer';
        $coco->code = 'coco';
        $coco->labels = ['fr_FR' => 'Coco Chanel'];
        $coco->values = [];
        $coco->completenessPercentage = null;
        $coco->image = [
            'filePath' => null,
            'originalFilename' => null
        ];

        $this->assertRecordItem($starck, $recordItems[0]);
        $this->assertRecordItem($coco, $recordItems[1]);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $starkCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $starkCode);
        $recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $referenceEntityIdentifier,
                $starkCode,
                ['fr_Fr' => 'Philippe Starck'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        );
        $cocoCode = RecordCode::fromString('coco');
        $this->cocoIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $cocoCode);
        $recordRepository->create(
            Record::create(
                $this->cocoIdentifier,
                $referenceEntityIdentifier,
                $cocoCode,
                ['fr_Fr' => 'Coco Chanel'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        );
    }

    private function assertRecordItem(RecordItem $expected, RecordItem $actual): void
    {
        $this->assertEquals($expected->identifier, $actual->identifier, 'Record identifiers are not equal');
        $this->assertEquals(
            $expected->referenceEntityIdentifier,
            $actual->referenceEntityIdentifier,
            'Reference entity identifier are not the same'
        );
        $expectedLabels = $expected->labels;
        $actualLabels = $actual->labels;
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            ),
            'Labels for the record item are not the same'
        );
        $this->assertEquals(
            $expected->values,
            $actual->values,
            'Values are not the same'
        );
        $this->assertEquals(
            $expected->image,
            $actual->image,
            'Image are not the same'
        );
        $this->assertEquals($expected->completenessPercentage, $actual->completenessPercentage);
    }
}

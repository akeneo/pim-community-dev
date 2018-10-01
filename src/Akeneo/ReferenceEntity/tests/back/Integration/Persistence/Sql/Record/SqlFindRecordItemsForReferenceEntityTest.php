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
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindRecordItemsForReferenceEntityTest extends SqlIntegrationTestCase
{
    /** @var FindRecordItemsForReferenceEntityInterface */
    private $findRecordsForReferenceEntity;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $cocoIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->findRecordsForReferenceEntity = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_items_for_reference_entity');
        $this->resetDB();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_records_corresponding_to_the_identifier()
    {
        $this->assertEmpty(($this->findRecordsForReferenceEntity)(ReferenceEntityIdentifier::fromString('unknown_reference_entity')));
    }

    /**
     * @test
     */
    public function it_returns_all_the_records_for_an_reference_entity()
    {
        $recordItems = ($this->findRecordsForReferenceEntity)(ReferenceEntityIdentifier::fromString('designer'));

        $starck = new RecordItem();
        $starck->identifier = $this->starckIdentifier;
        $starck->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $starck->code = RecordCode::fromString('stark');
        $starck->labels = LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']);

        $coco = new RecordItem();
        $coco->identifier = $this->cocoIdentifier;
        $coco->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $coco->code = RecordCode::fromString('coco');
        $coco->labels = LabelCollection::fromArray(['fr_FR' => 'Coco Chanel']);

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

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        $starckCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $starckCode);
        $recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $referenceEntityIdentifier,
                $starckCode,
                ['fr_FR' => 'Philippe Starck'],
                Image::fromFileInfo($imageInfo),
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
                ['fr_FR' => 'Coco Chanel'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        );
    }

    private function assertRecordItem(RecordItem $expected, RecordItem $actual): void
    {
        $this->assertTrue($expected->identifier->equals($actual->identifier), 'Record identifiers are not equal');
        $this->assertEquals(
            $expected->referenceEntityIdentifier,
            $actual->referenceEntityIdentifier,
            'Reference entity identifier are not the same'
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

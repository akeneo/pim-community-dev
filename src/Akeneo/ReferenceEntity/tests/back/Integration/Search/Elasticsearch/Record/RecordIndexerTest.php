<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * Testing the search usecases for the record grid for information in the code of the record.
 *
 * @see       https://akeneo.atlassian.net/wiki/spaces/AKN/pages/572424236/Search+an+entity+record
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexerTest extends SearchIntegrationTestCase
{
    /** @var RecordIndexerInterface */
    protected $recordIndexer;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->recordIndexer = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer');
    }

    /**
     * @test
     */
    public function it_indexes_one_record()
    {
        $record = Record::create(
            RecordIdentifier::fromString('designer_dyson_uuid4'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('dyson'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordIndexer->bulkIndex([$record]);

        $this->searchRecordIndexHelper->assertRecordExists('designer', 'dyson');
        Assert::assertCount(3, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
    }

    /**
     * @test
     */
    public function it_indexes_multiple_records()
    {
        $recordDyson = Record::create(
            RecordIdentifier::fromString('designer_dyson_uuid4'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('dyson'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $recordArad = Record::create(
            RecordIdentifier::fromString('designer_arad_uuid5'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('arad'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordIndexer->bulkIndex([$recordDyson, $recordArad]);

        $this->searchRecordIndexHelper->assertRecordExists('designer', 'dyson');
        $this->searchRecordIndexHelper->assertRecordExists('designer', 'arad');
        Assert::assertCount(4, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
    }

    /**
     * @test
     */
    public function it_does_nothing_when_indexing_empty_array()
    {
        $this->recordIndexer->bulkIndex([]);

        Assert::assertCount(2, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
    }

    /**
     * @test
     */
    public function it_deletes_one_record()
    {
        $this->recordIndexer->removeRecordByReferenceEntityIdentifierAndCode('designer', 'stark');

        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordExists('designer', 'coco');
        Assert::assertCount(1, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
        Assert::assertCount(1, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('manufacturer'));
    }

    /**
     * @test
     */
    public function it_deletes_all_reference_entity_records()
    {
        $this->recordIndexer->removeByReferenceEntityIdentifier('designer');

        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'stark');
        $this->searchRecordIndexHelper->assertRecordDoesNotExists('designer', 'coco');
        Assert::assertCount(0, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('designer'));
        Assert::assertCount(1, $this->searchRecordIndexHelper->findRecordsByReferenceEntity('manufacturer'));
    }

    private function loadFixtures()
    {
        $rightCode = [
            'identifier' => 'designer_stark_uuid1',
            'code' => 'stark',
            'reference_entity_code' => 'designer',
            'record_list_search' => ['ecommerce' => ['fr_FR' => 'stark']]
        ];
        $wrongCode = [
            'identifier' => 'designer_coco_uuid2',
            'code' => 'coco',
            'reference_entity_code' => 'designer',
            'record_list_search' => ['ecommerce' => ['fr_FR' => 'coco']],
        ];
        $wrongEnrichedEntity = [
            'identifier' => 'manufacturer_stark_uuid3',
            'code' => 'stark',
            'reference_entity_code' => 'manufacturer',
            'record_list_search' => ['ecommerce' => ['fr_FR' => 'stark']],
        ];
        $this->searchRecordIndexHelper->index([$rightCode, $wrongCode, $wrongEnrichedEntity]);
    }
}

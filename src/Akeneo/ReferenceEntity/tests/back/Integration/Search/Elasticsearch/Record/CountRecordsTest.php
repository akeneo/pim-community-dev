<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CountRecordsTest extends SearchIntegrationTestCase
{
    /** @var CountRecordsInterface */
    private $countRecords;

    /** @var ReferenceEntityIdentifier */
    private $emptyReferenceEntityIdentifier;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifiersWithRecords;

    public function setUp(): void
    {
        parent::setUp();

        $this->countRecords = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.count_records');
        $this->resetDB();
        $this->createReferenceEntityWithAttributes();
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_counts_the_number_of_records_for_a_reference_entity()
    {
        Assert::assertThat(
            $this->countRecords->forReferenceEntity($this->emptyReferenceEntityIdentifier),
            Assert::isEmpty()
        );
        Assert::assertThat(
            $this->countRecords->forReferenceEntity($this->referenceEntityIdentifiersWithRecords),
            Assert::equalTo(2)
        );
    }

    private function createReferenceEntityWithAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->emptyReferenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $this->emptyReferenceEntityIdentifier,
                [],
                Image::createEmpty()
            )
        );
        $this->referenceEntityIdentifiersWithRecords = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $this->referenceEntityIdentifiersWithRecords,
                [],
                Image::createEmpty()
            )
        );

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString('starck_designer'),
                $this->referenceEntityIdentifiersWithRecords,
                RecordCode::fromString('stark'),
                ValueCollection::fromValues([])
            )
        );
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString('kartell_designer'),
                $this->referenceEntityIdentifiersWithRecords,
                RecordCode::fromString('kartell'),
                ValueCollection::fromValues([])
            )
        );
        $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record_indexer')->refresh();
    }
}

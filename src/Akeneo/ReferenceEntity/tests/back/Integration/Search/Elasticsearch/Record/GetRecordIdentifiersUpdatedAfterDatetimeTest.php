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
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\GetRecordIdentifiersUpdatedAfterDatetime;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class GetRecordIdentifiersUpdatedAfterDatetimeTest extends SearchIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadDatabase();
    }

    public function it_batch_records()
    {
        $datetime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700');
        $recordIdentifiersInBatch = $this->getQuery()->nextBatch($this->getClient(), $datetime, 2);
        Assert::assertSame(['dyson_designer', 'kartell_designer'], $recordIdentifiersInBatch[0]);
        Assert::assertSame(['newson_designer', 'starck_designer'], $recordIdentifiersInBatch[1]);

        $recordIdentifiersInBatch = $this->getQuery()->nextBatch($this->getClient(), $datetime, 3);
        Assert::assertSame(['dyson_designer', 'kartell_designer', 'newson_designer'], $recordIdentifiersInBatch[0]);
        Assert::assertSame(['newson_designer'], $recordIdentifiersInBatch[1]);
    }

    /**
     * @test
     */
    public function it_return_records_updated_after_the_given_datetime()
    {
        $this->assertRecordIdentifiersReturned(
            ['dyson_designer', 'kartell_designer', 'newson_designer', 'starck_designer'],
            '2021-08-04T01:53:21-0700'
        );

        $this->assertRecordIdentifiersReturned(
            ['kartell_designer', 'newson_designer'],
            '2021-08-06T01:53:21-0700'
        );

        $this->assertRecordIdentifiersReturned(
            ['newson_designer'],
            '2021-08-06T02:53:21-0700'
        );


        $this->assertRecordIdentifiersReturned(
            [],
            '2021-08-06T02:53:22-0700'
        );
    }

    private function loadDatabase(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $designerIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $this->getReferenceEntityRepository()->create(ReferenceEntity::create($designerIdentifier, [], Image::createEmpty()));

        $this->createRecordUpdatedAt($designerIdentifier, 'starck_designer', 'stark', '2021-08-04T01:53:22-0700');
        $this->createRecordUpdatedAt($designerIdentifier, 'dyson_designer', 'dyson', '2021-08-05T01:53:22-0700');
        $this->createRecordUpdatedAt($designerIdentifier, 'kartell_designer', 'kartell', '2021-08-06T01:53:22-0700');
        $this->createRecordUpdatedAt($designerIdentifier, 'newson_designer', 'newson', '2021-08-06T02:53:22-0700');

        $this->getClient()->refreshIndex();
    }

    private function createRecordUpdatedAt(
        ReferenceEntityIdentifier $designerIdentifier,
        string $normalizedRecordIdentifier,
        string $normalizedRecordCode,
        string $normalizedUpdatedDatetime
    ) {
        $this->getRecordRepository()->create(
            Record::create(
                RecordIdentifier::fromString($normalizedRecordIdentifier),
                $designerIdentifier,
                RecordCode::fromString($normalizedRecordCode),
                ValueCollection::fromValues([]),
            )
        );

        $this->getRecordRepository()->update(
            Record::fromState(
                RecordIdentifier::fromString($normalizedRecordIdentifier),
                $designerIdentifier,
                RecordCode::fromString($normalizedRecordCode),
                ValueCollection::fromValues([]),
                \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700'),
                \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, $normalizedUpdatedDatetime)
            )
        );
    }

    private function getReferenceEntityRepository(): ReferenceEntityRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
    }

    private function getQuery(): GetRecordIdentifiersUpdatedAfterDatetime
    {
        return $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.get_record_identifiers_updated_after_datetime');
    }

    private function getClient(): Client
    {
        return $this->get('akeneo_referenceentity.client.record');
    }

    private function assertRecordIdentifiersReturned(
        array $expectedRecordIdentifiers,
        string $normalizedDatetime
    ) {
        $datetime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, $normalizedDatetime);
        $recordIdentifiersInBatch = $this->getQuery()->nextBatch($this->getClient(), $datetime, 1000);

        Assert::assertSame($expectedRecordIdentifiers, iterator_to_array($recordIdentifiersInBatch)[0] ?? []);
    }
}

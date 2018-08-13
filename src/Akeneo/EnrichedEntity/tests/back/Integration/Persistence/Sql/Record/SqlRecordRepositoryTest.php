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
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;

class SqlRecordRepositoryTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $this->resetDB();
        $this->insertEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_creates_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::create('designer', 'starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $record = Record::create(
            $identifier, $enrichedEntityIdentifier, RecordCode::fromString('starck'),
            ['en_US' => 'Starck', 'fr_FR' => 'Starck']
        );

        $this->repository->create($record);

        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertRecord($record, $recordFound);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_a_record_with_the_same_identifier()
    {
        $identifier = RecordIdentifier::create('designer', 'starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $record = Record::create(
            $identifier, $enrichedEntityIdentifier, RecordCode::fromString('starck'),
            ['en_US' => 'Starck', 'fr_FR' => 'Starck']
        );
        $this->repository->create($record);

        $this->expectException(DBALException::class);
        $this->repository->create($record);
    }


    /**
     * @test
     */
    public function it_updates_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::create('designer', 'starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $record = Record::create(
            $identifier, $enrichedEntityIdentifier, RecordCode::fromString('starck'),
            ['en_US' => 'Starck', 'fr_FR' => 'Starck']
        );
        $this->repository->create($record);
        $record->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Coco']));

        $this->repository->update($record);
        $recordFound = $this->repository->getByIdentifier($identifier);

        $this->assertRecord($record, $recordFound);
    }

    /**
     * @test
     */
    public function it_throws_when_updating_a_non_existing_record()
    {
        $identifier = RecordIdentifier::create('designer', 'starck');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $record = Record::create(
            $identifier, $enrichedEntityIdentifier, RecordCode::fromString('starck'),
            ['en_US' => 'Starck', 'fr_FR' => 'Starck']
        );

        $this->expectException(\RuntimeException::class);
        $this->repository->update($record);
    }

    /**
     * @test
     */
    public function it_counts_the_records()
    {
        $this->assertEquals(0, $this->repository->count());

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $identifier = RecordIdentifier::create('designer', 'record_identifier');
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            RecordCode::fromString('record_identifier'),
            []
        );

        $this->repository->create($record);

        $this->assertEquals(1, $this->repository->count());

        $identifier = RecordIdentifier::create('designer', 'record_identifier2');
        $record = Record::create(
            $identifier,
            $enrichedEntityIdentifier,
            RecordCode::fromString('record_identifier2'),
            []
        );

        $this->repository->create($record);

        $this->assertEquals(2, $this->repository->count());
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(RecordNotFoundException::class);

        $identifier = RecordIdentifier::create('designer', 'unknown_identifier');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $this->repository->getByIdentifier($identifier);
    }

    private function assertRecord(
        Record $expectedRecord,
        Record $recordFound
    ): void {
        $this->assertTrue($expectedRecord->equals($recordFound));
        $labelCodesExpected = $expectedRecord->getLabelCodes();
        $labelCodesFound = $recordFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach ($expectedRecord->getLabelCodes() as $localeCode) {
            $this->assertEquals($expectedRecord->getLabel($localeCode),
                $recordFound->getLabel($localeCode));
        }
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function insertEnrichedEntity(): void
    {
        $repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ]
        );
        $repository->create($enrichedEntity);
    }
}

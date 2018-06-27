<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Tests\Back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepository;
use Akeneo\Test\Integration\TestCase;

class SqlRecordRepositoryTest extends TestCase
{
    /** @var RecordRepository */
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
    public function it_saves_a_record_and_returns_it()
    {
        $identifier = RecordIdentifier::fromString('starck');
        $record = Record::create(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            ['en_US' => 'Starck', 'fr_FR' => 'Starck']
        );

        $this->repository->save($record);

        $recordFound = $this->repository->getByIdentifier($identifier);
        $this->assertRecord($record, $recordFound);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EntityNotFoundException::class);
        $this->repository->getByIdentifier(RecordIdentifier::fromString('unknown_identifier'));
    }

    /**
     * @test
     */
    public function it_updates_a_record()
    {
        $record = Record::create(
            RecordIdentifier::fromString('starck'),
            EnrichedEntityIdentifier::fromString('designer'),
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']
        );
        $this->repository->save($record);

        $record->updateLabels(
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Styliste',
            ])
        );
        $this->repository->save($record);

        $enrichedEntityFound = $this->repository->getByIdentifier(RecordIdentifier::fromString('starck'));
        $this->assertRecord($record, $enrichedEntityFound);
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

    protected function getConfiguration()
    {
        return null;
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_record;
SQL;

        $this->get('database_connection')->executeQuery($resetQuery);
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
        $repository->save($enrichedEntity);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record\RefreshRecords;

use Akeneo\ReferenceEntity\back\Infrastructure\Persistence\Sql\Record\RefreshRecords\AllRecordIdentifiers;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class AllRecordIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var AllRecordIdentifiers */
    private $allRecordIdentifiers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->allRecordIdentifiers = $this->get('akeneo_referenceentity.infrastructure.persistence.cli.all_records_identifiers');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_no_record_identifiers(): void
    {
        $this->assertEmpty(iterator_to_array($this->allRecordIdentifiers->fetch()));
    }

    /**
     * @test
     */
    public function it_returns_all_record_identifiers(): void
    {
        $this->createRecords(['red', 'blue']);
        $this->assertRecordsIdentifiers(['red', 'blue'], iterator_to_array($this->allRecordIdentifiers->fetch()));
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createRecords(array $recordCodes): void
    {
        /** @var ReferenceEntityRepositoryInterface $referenceEntityRepository */
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $referenceEntityIdentifier,
                [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
                Image::createEmpty()
            )
        );

        foreach ($recordCodes as $recordCode) {
            $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString($recordCode),
                    $referenceEntityIdentifier,
                    RecordCode::fromString($recordCode),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    /**
     * @param array $expectedIdentifiers
     * @param RecordIdentifier[] $actualIdentifiers
     */
    private function assertRecordsIdentifiers(array $expectedIdentifiers, array $actualIdentifiers): void
    {
        $normalizedIdentifiers = array_map(function (RecordIdentifier $identifier) {
            return $identifier->normalize();
        }, $actualIdentifiers);
        sort($normalizedIdentifiers);
        sort($expectedIdentifiers);
        $this->assertEquals($expectedIdentifiers, $normalizedIdentifiers);
    }
}

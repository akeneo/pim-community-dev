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

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfRecordsPerReferenceEntityTest extends SqlIntegrationTestCase
{
    /** @var AverageMaxQuery */
    private $averageMaxNumberOfRecordsPerReferenceEntity;

    public function setUp()
    {
        parent::setUp();

        $this->averageMaxNumberOfRecordsPerReferenceEntity = $this->get('akeneo_referenceentity.infrastructure.persistence.query.analytics.average_max_number_of_records_per_reference_entity');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_records_per_reference_entity()
    {
        $this->loadRecordsForReferenceEntity(2);
        $this->loadRecordsForReferenceEntity(4);

        $volume = $this->averageMaxNumberOfRecordsPerReferenceEntity->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('3', $volume->getAverageVolume());
        $this->assertEquals('average_max_records_per_reference_entity', $volume->getVolumeName());
        $this->assertFalse($volume->hasWarning(), 'There shouldn\'t be a warning for this reference entity volume');
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadRecordsForReferenceEntity(int $numberOfRecordsPerReferenceEntitiestoLoad): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($this->getRandomIdentifier());
        $referenceEntityRepository->create(ReferenceEntity::create(
            $referenceEntityIdentifier,
            [],
            Image::createEmpty()
        ));

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        for ($i=0; $i < $numberOfRecordsPerReferenceEntitiestoLoad; $i++) {
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString(sprintf('%s_%d', $i, $referenceEntityIdentifier->normalize())),
                    $referenceEntityIdentifier,
                    RecordCode::fromString(sprintf('%s_%d', $i, $referenceEntityIdentifier->normalize())),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    private function getRandomIdentifier(): string
    {
        return str_replace('-', '_', Uuid::uuid4()->toString());
    }
}

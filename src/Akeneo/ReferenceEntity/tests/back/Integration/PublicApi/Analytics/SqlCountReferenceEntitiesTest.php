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

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Analytics;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlCountReferenceEntities;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlCountReferenceEntitiesTest extends SqlIntegrationTestCase
{
    /** @var SqlCountReferenceEntities */
    private $countReferenceEntities;

    public function setUp()
    {
        parent::setUp();

        $this->countReferenceEntities = $this->get('akeneo_referenceentity.infrastructure.persistence.query.analytics.count_reference_entities');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_number_of_reference_entities_without_warning()
    {
        $this->loadReferenceEntities(2);
        $volume = $this->countReferenceEntities->fetch();
        $this->assertEquals('2', $volume->getVolume());
    }

    private function loadReferenceEntities(int $numberOfReferenceEntitiestoLoad): void
    {
        for ($i = 0; $i < $numberOfReferenceEntitiestoLoad; $i++) {
            $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
            $referenceEntity = ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString(sprintf('%d', $i)),
                [],
                Image::createEmpty()
            );
            $referenceEntityRepository->create($referenceEntity);
        }
    }
}

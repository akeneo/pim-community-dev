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

namespace Akeneo\EnrichedEntity\Integration\Persistence\Sql\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityExistsInterface;
use Akeneo\EnrichedEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlEnrichedEntityExistsTest extends SqlIntegrationTestCase
{
    /** @var EnrichedEntityExistsInterface */
    private $enrichedEntityExists;

    public function setUp()
    {
        parent::setUp();

        $this->enrichedEntityExists = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.enriched_entity_exists');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_identifier()
    {
        $this->loadEnrichedEntityDesigner();
        $this->assertTrue($this->enrichedEntityExists->withIdentifier(EnrichedEntityIdentifier::fromString('designer')));
        $this->assertFalse($this->enrichedEntityExists->withIdentifier(EnrichedEntityIdentifier::fromString('manufacturer')));
    }

    private function resetDB(): void
    {
        $this->get('akeneoenriched_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityDesigner(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $enrichedEntityRepository->create($enrichedEntity);
    }
}

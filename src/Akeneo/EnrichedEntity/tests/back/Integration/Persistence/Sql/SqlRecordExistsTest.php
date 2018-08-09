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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\RecordExistsInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordExistsTest extends SqlIntegrationTestCase
{
    /** @var RecordExistsInterface */
    private $recordExists;

    public function setUp()
    {
        parent::setUp();

        $this->recordExists = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.record_exists');
        $this->resetDB();
        $this->loadEnrichedEntityDesigner();
        $this->loadRecordStarck();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_identifier()
    {
        $this->assertTrue($this->recordExists->withIdentifier(RecordIdentifier::create('designer', 'starck')));
        $this->assertFalse($this->recordExists->withIdentifier(RecordIdentifier::create('designer', 'Coco')));
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityDesigner(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);
    }

    public function loadRecordStarck(): void
    {
        $recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::create('designer', 'starck'), EnrichedEntityIdentifier::fromString('designer'),
                RecordCode::fromString('starck'), ['fr_FR' => 'Philippe Starck']
            )
        );
    }
}

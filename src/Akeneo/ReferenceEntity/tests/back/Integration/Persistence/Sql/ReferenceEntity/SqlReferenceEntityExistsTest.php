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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlReferenceEntityExistsTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityExists = $this->get('akeneo_referenceentity.infrastructure.persistence.query.reference_entity_exists');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_identifier()
    {
        $this->loadReferenceEntityDesigner();
        $this->assertTrue($this->referenceEntityExists->withIdentifier(ReferenceEntityIdentifier::fromString('designer')));
        $this->assertFalse($this->referenceEntityExists->withIdentifier(ReferenceEntityIdentifier::fromString('manufacturer')));
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntityDesigner(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }
}

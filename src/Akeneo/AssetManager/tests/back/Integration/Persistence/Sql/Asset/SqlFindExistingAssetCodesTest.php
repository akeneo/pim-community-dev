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

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindExistingRecordCodesTest extends SqlIntegrationTestCase
{
    /** @var FindExistingRecordCodesInterface */
    private $existingRecordCodes;

    /** @var RecordIdentifier */
    private $recordIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->existingRecordCodes = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_existing_record_codes');
        $this->resetDB();

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_the_record_codes_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedRecordCodes = ['jacobs', 'starck'];

        $recordCodes = $this->existingRecordCodes->find($referenceEntityIdentifier, ['Coco', 'starck', 'jacobs']);
        $this->assertEquals($expectedRecordCodes, $recordCodes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->referenceEntity('designer')
            ->load();

        $this->fixturesLoader
            ->record('designer', 'starck')
            ->withValues([
                'label' => [
                    [
                        'channel' => null,
                        'locale' => 'fr_FR',
                        'data' => 'Philippe Starck',
                    ]
                ]
            ])
            ->load();

        $this->fixturesLoader
            ->record('designer', 'jacobs')
            ->withValues([
                'label' => [
                    [
                        'channel' => null,
                        'locale' => 'fr_FR',
                        'data' => 'Marc Jacobs',
                    ]
                ]
            ])
            ->load();
    }
}

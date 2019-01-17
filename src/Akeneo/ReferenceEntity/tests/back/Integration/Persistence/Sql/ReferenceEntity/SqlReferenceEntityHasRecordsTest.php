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
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityHasRecordsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlReferenceEntityHasRecordsTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityHasRecordsInterface */
    private $referenceEntityHasRecords;

    public function setUp()
    {
        parent::setUp();

        $this->referenceEntityHasRecords = $this->get('akeneo_referenceentity.infrastructure.persistence.query.reference_entity_has_records');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_tells_if_a_reference_entity_has_records()
    {
        $identifier = ReferenceEntityIdentifier::fromString('designer');
        $hasRecords = ($this->referenceEntityHasRecords)($identifier);
        $this->assertTrue($hasRecords);

        $identifier = ReferenceEntityIdentifier::fromString('brand');
        $hasRecords = ($this->referenceEntityHasRecords)($identifier);
        $this->assertFalse($hasRecords);
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $this->loadDesigners();
        $this->loadBrands();
    }

    private function loadDesigners(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('stark');
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode),
                $referenceEntityIdentifier,
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                ])
            )
        );
    }

    private function loadBrands(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }
}

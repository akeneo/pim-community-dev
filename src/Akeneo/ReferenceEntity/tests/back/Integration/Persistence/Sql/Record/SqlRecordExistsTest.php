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

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordExistsTest extends SqlIntegrationTestCase
{
    /** @var RecordExistsInterface */
    private $recordExists;

    /** @var RecordIdentifier */
    private $recordIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->recordExists = $this->get('akeneo_referenceentity.infrastructure.persistence.query.record_exists');
        $this->resetDB();
        $this->loadReferenceEntityDesigner();
        $this->loadRecordStarck();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_identifier()
    {
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('Coco');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $this->assertTrue($this->recordExists->withIdentifier($this->recordIdentifier));
        $this->assertFalse($this->recordExists->withIdentifier($recordIdentifier));
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

    public function loadRecordStarck(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordCode = RecordCode::fromString('starck');
        $this->recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $recordRepository->create(
            Record::create(
                $this->recordIdentifier,
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
}

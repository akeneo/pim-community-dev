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

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    /** @var RecordIdentifier */
    private $recordIdentifier;

    /** @var RecordCode */
    private $recordCode;

    public function setUp(): void
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
        $this->assertTrue($this->recordExists->withIdentifier($this->recordIdentifier));
        $this->assertFalse($this->recordExists->withIdentifier(RecordIdentifier::fromString('unknown_record_identifier')));
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_record_code_for_reference_entity()
    {
        $this->assertTrue($this->recordExists->withReferenceEntityAndCode($this->referenceEntityIdentifier, $this->recordCode));
        $this->assertFalse(
            $this->recordExists->withReferenceEntityAndCode($this->referenceEntityIdentifier, RecordCode::fromString('unknown'))
        );
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
        $this->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = $referenceEntityRepository->getByIdentifier($this->referenceEntityIdentifier);

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->recordCode = RecordCode::fromString('starck');
        $this->recordIdentifier = RecordIdentifier::fromString('stark_designer_fingerprint');

        $recordRepository->create(
            Record::create(
                $this->recordIdentifier,
                $this->referenceEntityIdentifier,
                $this->recordCode,
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

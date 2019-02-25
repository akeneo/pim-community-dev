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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
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
        $this->loadReferenceEntityDesigner();
        $this->loadRecords();
    }

    /**
     * @test
     */
    public function it_returns_the_record_codes_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedRecordCodes = ['jacobs', 'starck'];

        $recordCodes = ($this->existingRecordCodes)($referenceEntityIdentifier, ['Coco', 'starck', 'jacobs']);
        $this->assertEquals($expectedRecordCodes, $recordCodes);
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

    public function loadRecords(): void
    {
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $starkCode = RecordCode::fromString('starck');
        $starkIdentifier = RecordIdentifier::create('designer', 'stark', 'fingerprint');
        $recordRepository->create(
            Record::create(
                $starkIdentifier,
                $designerIdentifier,
                $starkCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                ])
            )
        );

        $jacobsCode = RecordCode::fromString('jacobs');
        $jacobsIdentifier = RecordIdentifier::create('designer', 'jacobs', 'fingerprint');
        $recordRepository->create(
            Record::create(
                $jacobsIdentifier,
                $designerIdentifier,
                $jacobsCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Marc Jacobs')
                    ),
                ])
            )
        );
    }
}

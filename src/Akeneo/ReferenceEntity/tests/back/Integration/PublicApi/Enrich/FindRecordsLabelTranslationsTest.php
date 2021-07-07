<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Enrich;

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
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindRecordsLabelTranslationsTest extends SqlIntegrationTestCase
{
    private FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations;

    public function setUp(): void
    {
        parent::setUp();

        $this->findRecordsLabelTranslations = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.query.enrich.find_records_labels_public_api'
        );
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_finds_all_records_labels()
    {
        $this->loadReferenceEntityAndRecords();
        $records = $this->findRecordsLabelTranslations->find('designer', ['michael', 'starck', 'dyson'], 'fr_FR');
        Assert::assertEquals(
            [
                'dyson' => 'Dyson',
                'michael' => null,
                'starck' => 'Philippe Starck',
            ],
            $records
        );
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $referenceEntityRepository = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.repository.reference_entity'
        );
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
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

        // Starck record
        $starckCode = RecordCode::fromString('starck');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $starckCode);
        $labelValueFR = Value::create(
            $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Philippe Starck')
        );
        $labelValueUS = Value::create(
            $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck US')
        );
        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValueFR, $labelValueUS])
            )
        );

        // Dyson record
        $dysonCode = RecordCode::fromString('dyson');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $dysonCode);
        $labelValueFR = Value::create(
            $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson')
        );
        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $dysonCode,
                ValueCollection::fromValues([$labelValueFR])
            )
        );

        // Michael record
        $michaelCode = RecordCode::fromString('michael');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $michaelCode);
        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $michaelCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

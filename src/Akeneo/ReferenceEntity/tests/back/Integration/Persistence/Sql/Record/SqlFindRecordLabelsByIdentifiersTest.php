<?php

declare(strict_types=1);

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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindRecordLabelsByIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var FindRecordLabelsByIdentifiersInterface */
    private $query;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $dysonIdentifier;

    /** @var RecordIdentifier */
    private $michaelIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_labels_by_identifiers');
        $this->resetDB();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_finds_record_labels_by_identifiers()
    {
        $result = $this->query->find([(string) $this->michaelIdentifier, (string) $this->dysonIdentifier]);
        $this->assertEquals([
            (string) $this->michaelIdentifier => [
                'labels' => ['fr_FR' => null, 'en_US' => null, 'de_DE' => null],
                'code' => 'michael'
            ],
            (string) $this->dysonIdentifier => [
                'labels' => ['fr_FR' => 'Dyson', 'en_US' => null, 'de_DE' => null],
                'code' => 'dyson'
            ],
        ], $result);

        $result = $this->query->find([(string) $this->starckIdentifier]);
        $this->assertEquals([
            (string) $this->starckIdentifier => [
                'labels' => ['fr_FR' => 'Philippe Starck', 'en_US' => 'Philippe Starck', 'de_DE' => null],
                'code' => 'starck'
            ],
        ], $result);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
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
        $this->starckIdentifier = $recordIdentifier;
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
            TextData::fromString('Philippe Starck')
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
        $this->dysonIdentifier = $recordIdentifier;
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
        $this->michaelIdentifier = $recordIdentifier;
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

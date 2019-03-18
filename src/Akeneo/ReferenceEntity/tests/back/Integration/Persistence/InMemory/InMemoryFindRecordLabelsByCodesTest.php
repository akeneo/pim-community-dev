<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRecordLabelsByCodes;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindReferenceEntityAttributeAsLabel;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindRecordLabelsByCodesTest extends TestCase
{
    /** @var InMemoryFindRecordLabelsByCodes */
    private $findRecordLabelsByCodesQuery;

    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->recordRepository = new InMemoryRecordRepository();
        $this->referenceEntityRepository = new InMemoryReferenceEntityRepository(new EventDispatcher());

        $this->findRecordLabelsByCodesQuery = new InMemoryFindRecordLabelsByCodes(
            $this->recordRepository,
            new InMemoryFindReferenceEntityAttributeAsLabel(
                $this->referenceEntityRepository
            )
        );

        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_finds_labels_for_given_record_codes()
    {
        $labels = $this->findRecordLabelsByCodesQuery->find(
            ReferenceEntityIdentifier::fromString('designer'),
            ['starck', 'dyson', 'michael']
        );

        $this->assertNotEmpty($labels);
        $this->assertContainsOnlyInstancesOf(LabelCollection::class, $labels);

        $this->assertEquals(
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck', 'en_US' => 'Philippe Starck']),
            $labels['starck']
        );

        $this->assertEquals(
            LabelCollection::fromArray(['fr_FR' => 'Dyson']),
            $labels['dyson']
        );

        $this->assertEquals(
            LabelCollection::fromArray([]),
            $labels['michael']
        );
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $referenceEntity->updateAttributeAsLabelReference(AttributeAsLabelReference::createFromNormalized('label'));

        // Starck record
        $starckCode = RecordCode::fromString('starck');
        $recordIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $starckCode);
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
        $this->recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValueFR, $labelValueUS])
            )
        );

        // Dyson record
        $dysonCode = RecordCode::fromString('dyson');
        $recordIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $dysonCode);
        $labelValueFR = Value::create(
            $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson')
        );
        $this->recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $dysonCode,
                ValueCollection::fromValues([$labelValueFR])
            )
        );

        // Michael record
        $michaelCode = RecordCode::fromString('michael');
        $recordIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $michaelCode);
        $this->recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $michaelCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

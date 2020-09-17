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

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Onboarder;

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
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder\FindAllRecordLabels;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder\RecordLabels;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindAllRecordLabelsTest extends SqlIntegrationTestCase
{
    /** @var FindAllRecordLabels*/
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

        $this->query = $this->get('akeneo_referenceentity.infrastructure.persistence.query.onboarder.find_all_record_labels');
        $this->resetDB();
        $this->loadReferenceEntityAndRecords();
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_finds_all_record_labels()
    {
        $records = $this->query->find();
        $records = iterator_to_array($records);
        Assert::assertContainsEquals(new RecordLabels(
            (string) $this->michaelIdentifier,
            ['fr_FR' => null, 'en_US' => null, 'de_DE' => null],
            'michael',
            'designer'
        ), $records);
        Assert::assertContainsEquals(new RecordLabels(
           (string) $this->starckIdentifier,
           ['fr_FR' => 'Philippe Starck', 'en_US' => 'Philippe Starck US', 'de_DE' => null],
           'starck',
           'designer'
        ), $records);
        Assert::assertContainsEquals(new RecordLabels(
           (string) $this->dysonIdentifier,
           ['fr_FR' => 'Dyson', 'en_US' => null, 'de_DE' => null],
           'dyson',
           'designer'
        ), $records);
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

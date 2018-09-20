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

namespace Akeneo\EnrichedEntity\Integration\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\TextData;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\Value;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\Integration\SqlIntegrationTestCase;

class SqlFindRecordDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindRecordDetailsInterface */
    private $findRecordDetailsQuery;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var RecordIdentifier */
    private $recordIdentifier;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->findRecordDetailsQuery = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $this->recordRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.record');
        $this->attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadEnrichedEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_records()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('unknown_enriched_entity');
        $recordCode = RecordCode::fromString('unknown_record_code');
        $this->assertNull(($this->findRecordDetailsQuery)($enrichedEntityIdentifier, $recordCode));
    }

    /**
     * @test
     */
    public function it_returns_the_record_details()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('starck');
        $actualStarck = ($this->findRecordDetailsQuery)($enrichedEntityIdentifier, $recordCode);
        $nameAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'name', 'fingerprint')
        );
        $descriptionAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'description', 'fingerprint')
        );

        $expectedValues = [
            'description_designer_fingerprint_de_DE' => [
                'data' => null,
                'locale' => 'de_DE',
                'channel' => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            'description_designer_fingerprint_en_US' => [
                'data' => null,
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            'description_designer_fingerprint_fr_FR' => [
                'data' => null,
                'locale' => 'fr_FR',
                'channel' => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            'name_designer_fingerprint' => [
                'data' => 'Hello',
                'locale' => null,
                'channel' => null,
                'attribute' => $nameAttribute->normalize(),
            ],
        ];

        $expectedStarck = new RecordDetails(
            $this->recordIdentifier,
            $enrichedEntityIdentifier,
            $recordCode,
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']),
            $expectedValues
        );

        $this->assertRecordDetails($expectedStarck, $actualStarck);
    }

    private function resetDB(): void
    {
        $this->get('akeneoenriched_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntityAndRecords(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $enrichedEntity = EnrichedEntity::create(
            $enrichedEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            null
        );
        $enrichedEntityRepository->create($enrichedEntity);

        $value = Value::create(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Hello')
        );

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($textAttribute);

        $localizedTextAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'description', 'fingerprint'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'description']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(2500),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($localizedTextAttribute);

        $starckCode = RecordCode::fromString('starck');
        $this->recordIdentifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $starckCode);
        $this->recordRepository->create(
            Record::create(
                $this->recordIdentifier,
                $enrichedEntityIdentifier,
                $starckCode,
                ['fr_FR' => 'Philippe Starck'],
                ValueCollection::fromValues([$value])
            )
        );
    }

    private function assertRecordDetails(RecordDetails $expected, RecordDetails $actual)
    {
        $this->assertSame($expected->normalize(), $actual->normalize());
    }
}

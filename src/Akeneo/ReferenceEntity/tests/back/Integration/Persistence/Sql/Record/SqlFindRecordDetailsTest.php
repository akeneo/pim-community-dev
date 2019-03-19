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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

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

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->findRecordDetailsQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_details');
        $this->recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_records()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('unknown_reference_entity');
        $recordCode = RecordCode::fromString('unknown_record_code');
        $this->assertNull(($this->findRecordDetailsQuery)($referenceEntityIdentifier, $recordCode));
    }

    /**
     * @test
     */
    public function it_returns_the_record_details()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);

        $recordCode = RecordCode::fromString('starck');
        $actualStarck = ($this->findRecordDetailsQuery)($referenceEntityIdentifier, $recordCode);
        $nameAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'name', 'fingerprint')
        );
        $descriptionAttribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'description', 'fingerprint')
        );
        $labelAttribute = $this->attributeRepository->getByIdentifier(
            $referenceEntity->getAttributeAsLabelReference()->getIdentifier()
        );
        $imageAttribute = $this->attributeRepository->getByIdentifier(
            $referenceEntity->getAttributeAsImageReference()->getIdentifier()
        );

        $expectedValues = [
            [
                'data' => null,
                'locale' => 'de_DE',
                'channel' => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            [
                'data' => null,
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            [
                'data' => null,
                'locale' => 'fr_FR',
                'channel' => null,
                'attribute' => $descriptionAttribute->normalize(),
            ],
            [
                'data' => 'Hello',
                'locale' => null,
                'channel' => null,
                'attribute' => $nameAttribute->normalize(),
            ],
            [
                'data' => 'Philippe Starck',
                'locale' => 'fr_FR',
                'channel' => null,
                'attribute' => $labelAttribute->normalize(),
            ],
            [
                'data' => null,
                'locale' => 'en_US',
                'channel' => null,
                'attribute' => $labelAttribute->normalize(),
            ],
            [
                'data' => null,
                'locale' => 'de_DE',
                'channel' => null,
                'attribute' => $labelAttribute->normalize(),
            ],
            [
                'data' => [
                    'filePath' => 'test/image_2.jpg',
                    'originalFilename' => 'image_2.jpg',
                    'size' => 100,
                    'mimeType' => 'image/jpg',
                    'extension' => '.jpg'
                ],
                'locale' => null,
                'channel' => null,
                'attribute' => $imageAttribute->normalize(),
            ],
        ];

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $expectedStarck = new RecordDetails(
            $this->recordIdentifier,
            $referenceEntityIdentifier,
            $recordCode,
            LabelCollection::fromArray(['fr_FR' => 'Philippe Starck']),
            Image::fromFileInfo($imageInfo),
            $expectedValues,
            true
        );

        $this->assertRecordDetails($expectedStarck, $actualStarck);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
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
        $labelValue = Value::create(
            $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Philippe Starck')
        );
        $imageValue = Value::create(
            $referenceEntity->getAttributeAsImageReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromNormalize([
                'filePath' => 'test/image_2.jpg',
                'originalFilename' => 'image_2.jpg',
                'size' => 100,
                'mimeType' => 'image/jpg',
                'extension' => '.jpg'
            ])
        );

        $value = Value::create(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Hello')
        );

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
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
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'description']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(2500),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($localizedTextAttribute);

        $starckCode = RecordCode::fromString('starck');
        $this->recordIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $starckCode);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $this->recordRepository->create(
            Record::create(
                $this->recordIdentifier,
                $referenceEntityIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValue, $imageValue, $value])
            )
        );
    }

    private function assertRecordDetails(RecordDetails $expected, RecordDetails $actual)
    {
        $this->assertEqualsCanonicalizing($expected->normalize(), $actual->normalize());
    }
}

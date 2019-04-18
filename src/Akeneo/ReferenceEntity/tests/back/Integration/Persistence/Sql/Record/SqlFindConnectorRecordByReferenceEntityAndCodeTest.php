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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorRecordByReferenceEntityAndCodeTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var FindConnectorRecordByReferenceEntityAndCodeInterface*/
    private $findConnectorRecordQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->findConnectorRecordQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_record_by_reference_entity_and_code');
        $this->resetDB();
        $this->createReferenceEntityWithAttributesAndRecords();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_record()
    {
        $record = $this->createStarckRecord();

        $expectedRecord = new ConnectorRecord(
            $record->getCode(),
            [
                'name'  => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'Philippe Stark',
                    ],
                    [
                        'locale'  => 'fr_FR',
                        'channel' => 'ecommerce',
                        'data'    => 'Philippe Stark',
                    ]
                ],
                'main_image' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'test/image_1.jpg',
                    ]
                ],
                'country' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'france',
                    ]
                ],
                'brands' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => ['lexon', 'kartell', 'cogip'],
                    ]
                ],
                'favorite_color' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'black',
                    ]
                ],
                'materials' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => ['plastic', 'metal'],
                    ]
                ],
            ]
        );

        $recordFound = ($this->findConnectorRecordQuery)(ReferenceEntityIdentifier::fromString('designer'), $record->getCode());

        $this->assertSameRecords($expectedRecord, $recordFound);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_record_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('Foo');

        $recordFound = ($this->findConnectorRecordQuery)($referenceEntityIdentifier, $recordCode);

        $this->assertNull($recordFound);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createStarckRecord(): Record
    {
        $recordCode = RecordCode::fromString('starck');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg')
            ->setSize(1024)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_designer_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    AttributeIdentifier::fromString('main_image_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('country_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    RecordData::createFromNormalize('france')
                ),
                Value::create(
                    AttributeIdentifier::fromString('brands_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    RecordCollectionData::createFromNormalize(['kartell', 'lexon', 'cogip'])
                ),
                Value::create(
                    AttributeIdentifier::fromString('favorite_color_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    OptionData::createFromNormalize('black')
                ),
                Value::create(
                    AttributeIdentifier::fromString('materials_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    OptionCollectionData::createFromNormalize(['plastic', 'metal'])
                ),
            ])
        );

        $this->recordRepository->create($record);

        return $record;
    }

    private function createReferenceEntityWithAttributesAndRecords(): void
    {
        $repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

        foreach (['designer', 'country', 'brand'] as $identifier) {
            $referenceEntityDesigner = ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString($identifier),
                [],
                Image::createEmpty()
            );
            $repository->create($referenceEntityDesigner);
        }

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $image = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'main_image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['png'])
        );

        $country = RecordAttribute::create(
            AttributeIdentifier::create('designer', 'country', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['en_US' => 'Country']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );

        $brands = RecordCollectionAttribute::create(
            AttributeIdentifier::create('designer', 'brands', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('brands'),
            LabelCollection::fromArray(['en_US' => 'Brands']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('brand')
        );

        $favoriteColor = OptionAttribute::create(
            AttributeIdentifier::create('designer', 'favorite_color', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray(['en_US' => 'Favorite color']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $favoriteColor->setOptions([
            AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('black'), LabelCollection::fromArray([])),
        ]);

        $materials = OptionCollectionAttribute::create(
            AttributeIdentifier::create('designer', 'materials', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('materials'),
            LabelCollection::fromArray(['en_US' => 'Materials']),
            AttributeOrder::fromInteger(7),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $materials->setOptions([
            AttributeOption::create(OptionCode::fromString('metal'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('plastic'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('wood'), LabelCollection::fromArray([])),
        ]);

        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
        $attributesRepository->create($country);
        $attributesRepository->create($brands);
        $attributesRepository->create($favoriteColor);
        $attributesRepository->create($materials);

        $countryRecord = Record::create(
            RecordIdentifier::fromString('country_france_fingerprint'),
            ReferenceEntityIdentifier::fromString('country'),
            RecordCode::fromString('france'),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('label_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('France')
                ),
            ])
        );
        $this->recordRepository->create($countryRecord);

        foreach (['kartell', 'lexon', 'cogip'] as $code) {
            $brandRecord = Record::create(
                RecordIdentifier::fromString(sprintf('brand_%s_fingerprint', $code)),
                ReferenceEntityIdentifier::fromString('brand'),
                RecordCode::fromString($code),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(ucfirst($code))
                    ),
                ])
            );
            $this->recordRepository->create($brandRecord);
        }
    }

    private function assertSameRecords(ConnectorRecord $expectedRecord, ConnectorRecord $currentRecord): void
    {
        $expectedRecord = $expectedRecord->normalize();
        $expectedRecord['values'] = $this->sortRecordValues($expectedRecord['values']);

        $currentRecord = $currentRecord->normalize();
        $currentRecord['values'] = $this->sortRecordValues($currentRecord['values']);

        $this->assertEquals($expectedRecord, $currentRecord);
    }

    private function sortRecordValues(array $recordValues): array
    {
        ksort($recordValues);

        foreach ($recordValues as $attributeCode => $recordValue) {
            foreach ($recordValue as $key => $value) {
                if (is_array($value['data'])) {
                    sort($value['data']);
                    $recordValues[$attributeCode][$key] = $value['data'];
                }
            }
        }

        return $recordValues;
    }
}

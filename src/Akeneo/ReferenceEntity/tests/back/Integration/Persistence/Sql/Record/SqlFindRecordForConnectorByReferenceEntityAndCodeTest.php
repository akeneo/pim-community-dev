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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordForConnectorByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindRecordForConnectorByReferenceEntityAndCodeTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $repository;

    /** @var FindRecordForConnectorByReferenceEntityAndCodeInterface*/
    private $findRecordForConnectorQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->findRecordForConnectorQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_for_connector_by_reference_entity_and_code');
        $this->resetDB();
        $this->createReferenceEntityWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_a_record_for_connector()
    {
        $record = $this->createStarckRecord();

        $expectedRecord = new RecordForConnector(
            $record->getCode(),
            LabelCollection::fromArray(['en_US' => 'Starck', 'fr_FR' => 'Starck']),
            $record->getImage(),
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
                'image' => [
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
                ]
            ]
        );

        $recordFound = ($this->findRecordForConnectorQuery)(ReferenceEntityIdentifier::fromString('designer'), $record->getCode());

        $this->assertSameRecords($expectedRecord, $recordFound);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_record_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('Foo');

        $recordFound = ($this->findRecordForConnectorQuery)($referenceEntityIdentifier, $recordCode);

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
        $identifier = $this->repository->nextIdentifier($referenceEntityIdentifier, $recordCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg')
            ->setSize(1024)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ['en_US' => 'Starck', 'fr_FR' => 'Starck'],
            Image::fromFileInfo($imageInfo),
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
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
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
            ])
        );

        $this->repository->create($record);

        return $record;
    }

    private function createReferenceEntityWithAttributes(): void
    {
        $repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        );
        $repository->create($referenceEntity);

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $image = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(1),
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
            AttributeOrder::fromInteger(2),
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
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('brand')
        );

        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
        $attributesRepository->create($country);
        $attributesRepository->create($brands);
    }

    private function assertSameRecords(RecordForConnector $expectedRecord, RecordForConnector $currentRecord): void
    {
        $expectedRecord = $expectedRecord->normalize();
        $expectedRecord['values'] = $this->sortRecordValues($expectedRecord['values']);

        $currentRecord = $currentRecord->normalize();
        $currentRecord['values'] = $this->sortRecordValues($currentRecord['values']);

        $this->assertSame($expectedRecord, $currentRecord);
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

<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Enrich;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
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
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\NumberData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsAttributeValueInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;

class FindRecordsAttributeValueTest extends SqlIntegrationTestCase
{
    private FindRecordsAttributeValueInterface $findRecordsAttributeValue;
    private AttributeIdentifier $labelAttributeIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->findRecordsAttributeValue = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.query.enrich.find_records_attribute_value_public_api'
        );
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_finds_provided_records_and_text_attribute_value(): void
    {
        $records = $this->findRecordsAttributeValue->find(
            'designer',
            [
                'michael',
                'starck',
                'dyson',
                'unknown_record',
            ],
            $this->labelAttributeIdentifier->normalize(),
            null,
            'fr_FR',
        );
        Assert::assertEquals(
            [
                'dyson' => 'Dyson',
                'michael' => null,
                'starck' => 'Philippe Starck',
            ],
            $records,
        );

        $records = $this->findRecordsAttributeValue->find(
            'designer',
            [
                'dyson',
                'starck',
            ],
            'text_attribute_designer_fingerprint',
            'ecommerce',
            'fr_FR',
        );
        Assert::assertEquals(
            [
                'dyson' => 'Joli texte oui',
                'starck' => null,
            ],
            $records,
        );
    }

    /**
     * @test
     */
    public function it_finds_provided_records_and_number_attribute_value(): void
    {
        $records = $this->findRecordsAttributeValue->find(
            'designer',
            ['starck', 'dyson'],
            'number_attribute_designer_fingerprint',
            null,
            null,
        );
        Assert::assertEquals(
            [
                'dyson' => '2',
                'starck' => null,
            ],
            $records,
        );
    }

    /**
     * @test
     */
    public function it_finds_provided_records_and_image_attribute_value(): void
    {
        $records = $this->findRecordsAttributeValue->find(
            'designer',
            ['starck', 'dyson'],
            'image_attribute_designer_fingerprint',
            null,
            null,
        );
        Assert::assertEquals(
            [
                'dyson' => [
                    'filePath' => '/a/b/c/philou.png',
                    'originalFilename' => 'philou.png',
                    'size' => null,
                    'mimeType' => null,
                    'extension' => null,
                ],
                'starck' => null,
            ],
            $records,
        );
    }

    /**
     * @test
     */
    public function it_finds_provided_records_and_option_attribute_value(): void
    {
        $records = $this->findRecordsAttributeValue->find(
            'designer',
            ['starck', 'dyson'],
            'option_attribute_designer_fingerprint',
            null,
            null,
        );
        Assert::assertEquals(
            [
                'dyson' => 'red',
                'starck' => null,
            ],
            $records,
        );
    }

    /**
     * @test
     */
    public function it_finds_provided_records_and_option_collection_attribute_value(): void
    {
        $records = $this->findRecordsAttributeValue->find(
            'designer',
            ['starck', 'dyson'],
            'option_col_attribute_designer_fingerprint',
            null,
            null,
        );
        Assert::assertEquals(
            [
                'dyson' => ['red', 'blue'],
                'starck' => null,
            ],
            $records,
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
            Image::createEmpty(),
        );
        $referenceEntityRepository->create($referenceEntity);
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $this->labelAttributeIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier();
        $textAttributeEcommerceFR = $this->createTextAttribute((string) $referenceEntityIdentifier, 'text_attribute', 10, true, true);
        $numberAttribute = $this->createNumberAttribute((string) $referenceEntityIdentifier, 'number_attribute', 20, false, false);
        $imageAttribute = $this->createImageAttribute((string) $referenceEntityIdentifier, 'image_attribute', 30, false, false);
        $optionAttribute = $this->createOptionAttribute((string) $referenceEntityIdentifier, 'option_attribute', ['blue', 'red'], 40, false, false);
        $optionCollectionAttribute = $this->createOptionCollectionAttribute((string) $referenceEntityIdentifier, 'option_col_attribute', ['blue', 'red'], 50, false, false);

        // Starck record
        $starckCode = RecordCode::fromString('starck');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $starckCode);
        $labelValueFR = Value::create(
            $this->labelAttributeIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Philippe Starck'),
        );
        $labelValueUS = Value::create(
            $this->labelAttributeIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Philippe Starck US'),
        );
        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $starckCode,
                ValueCollection::fromValues([$labelValueFR, $labelValueUS]),
            ),
        );

        // Dyson record
        $dysonCode = RecordCode::fromString('dyson');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $dysonCode);
        $labelValueFR = Value::create(
            $this->labelAttributeIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson'),
        );
        $textAttributeValueEcommerceFR = Value::create(
            $textAttributeEcommerceFR->getIdentifier(),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Joli texte oui'),
        );
        $numberAttributeValue = Value::create(
            $numberAttribute->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            NumberData::fromString('2'),
        );
        $imageAttributeValue = Value::create(
            $imageAttribute->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo((new FileInfo())->setOriginalFilename('philou.png')->setKey('/a/b/c/philou.png')),
        );
        $optionAttributeValue = Value::create(
            $optionAttribute->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionData::createFromNormalize('red'),
        );
        $optionCollectionAttributeValue = Value::create(
            $optionCollectionAttribute->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionCollectionData::createFromNormalize(['red', 'blue'])
        );
        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $dysonCode,
                ValueCollection::fromValues([
                    $labelValueFR,
                    $textAttributeValueEcommerceFR,
                    $numberAttributeValue,
                    $imageAttributeValue,
                    $optionAttributeValue,
                    $optionCollectionAttributeValue,
                ]),
            ),
        );

        // Michael record
        $michaelCode = RecordCode::fromString('michael');
        $recordIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $michaelCode);
        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntityIdentifier,
                $michaelCode,
                ValueCollection::fromValues([]),
            ),
        );
    }

    private function createTextAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): TextAttribute {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = TextAttribute::createText(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($attribute);

        return $attribute;
    }

    private function createNumberAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): NumberAttribute {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = NumberAttribute::create(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            AttributeDecimalsAllowed::fromBoolean(true),
            AttributeLimit::limitless(),
            AttributeLimit::limitless(),
        );
        $attributeRepository->create($attribute);

        return $attribute;
    }

    private function createImageAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): ImageAttribute {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = ImageAttribute::create(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList([]),
        );
        $attributeRepository->create($attribute);

        return $attribute;
    }

    private function createOptionAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        array $options,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): OptionAttribute {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = OptionAttribute::create(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
        );
        $attribute->setOptions(array_map(
            static fn (string $optionCode) => AttributeOption::create(
                OptionCode::fromString($optionCode),
                LabelCollection::fromArray([]),
            ),
            $options,
        ));

        $attributeRepository->create($attribute);

        return $attribute;
    }

    private function createOptionCollectionAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        array $options,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): OptionCollectionAttribute {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
        );
        $attribute->setOptions(array_map(
            static fn (string $optionCode) => AttributeOption::create(
                OptionCode::fromString($optionCode),
                LabelCollection::fromArray([]),
            ),
            $options,
        ));

        $attributeRepository->create($attribute);

        return $attribute;
    }
}

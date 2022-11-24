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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindReferenceEntityAttributesInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class FindReferenceEntityAttributesTest extends SqlIntegrationTestCase
{
    private FindReferenceEntityAttributesInterface $findReferenceEntityAttributes;
    private string $referenceEntityLabelIdentifier = '';
    private string $referenceEntityImageIdentifier = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->findReferenceEntityAttributes = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.query.enrich.find_reference_entity_attributes_public_api'
        );
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntityWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_reference_entity_attributes(): void
    {
        $normalizedReferenceEntityAttributes = array_map(
            static fn ($attribute) => $attribute->normalize(),
            $this->findReferenceEntityAttributes->findByCode('designer'),
        );

        $expectedNormalizedAttributes = [
            [
                'identifier' => $this->referenceEntityLabelIdentifier,
                'code' => 'label',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => true,
                'type' => 'text',
            ],
            [
                'identifier' => $this->referenceEntityImageIdentifier,
                'code' => 'image',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'image',
            ],
            [
                'identifier' => 'text_attribute_designer_fingerprint',
                'code' => 'text_attribute',
                'labels' => [],
                'value_per_channel' => true,
                'value_per_locale' => true,
                'type' => 'text',
            ],
            [
                'identifier' => 'number_attribute_designer_fingerprint',
                'code' => 'number_attribute',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'number',
            ],
            [
                'identifier' => 'image_attribute_designer_fingerprint',
                'code' => 'image_attribute',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'image',
            ],
            [
                'identifier' => 'option_attribute_designer_fingerprint',
                'code' => 'option_attribute',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'option',
            ],
            [
                'identifier' => 'option_collection_at_designer_fingerprint',
                'code' => 'option_collection_attribute',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'option_collection',
            ],
        ];

        Assert::assertEqualsCanonicalizing($expectedNormalizedAttributes, $normalizedReferenceEntityAttributes);
    }

    /**
     * @test
     */
    public function it_finds_reference_entity_attributes_filtered_on_type(): void
    {
        $normalizedReferenceEntityAttributes = array_map(
            static fn ($attribute) => $attribute->normalize(),
            $this->findReferenceEntityAttributes->findByCode('designer', ['text', 'image']),
        );

        $expectedNormalizedAttributes = [
            [
                'identifier' => $this->referenceEntityLabelIdentifier,
                'code' => 'label',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => true,
                'type' => 'text',
            ],
            [
                'identifier' => $this->referenceEntityImageIdentifier,
                'code' => 'image',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'image',
            ],
            [
                'identifier' => 'text_attribute_designer_fingerprint',
                'code' => 'text_attribute',
                'labels' => [],
                'value_per_channel' => true,
                'value_per_locale' => true,
                'type' => 'text',
            ],
            [
                'identifier' => 'image_attribute_designer_fingerprint',
                'code' => 'image_attribute',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'image',
            ],
        ];

        Assert::assertEqualsCanonicalizing($expectedNormalizedAttributes, $normalizedReferenceEntityAttributes);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_reference_entity_not_found(): void
    {
        $referenceEntityAttributes = $this->findReferenceEntityAttributes->findByCode('unknown');

        Assert::assertEmpty($referenceEntityAttributes);
    }

    private function loadReferenceEntityWithAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create($referenceEntityIdentifier, [], Image::createEmpty());
        $referenceEntityRepository->create($referenceEntity);
        /** @var ReferenceEntity $referenceEntity */
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $this->referenceEntityLabelIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier()->normalize();
        $this->referenceEntityImageIdentifier = $referenceEntity->getAttributeAsImageReference()->getIdentifier()->normalize();
        $this->createTextAttribute((string) $referenceEntityIdentifier, 'text_attribute', 10, true, true);
        $this->createNumberAttribute((string) $referenceEntityIdentifier, 'number_attribute', 20, false, false);
        $this->createImageAttribute((string) $referenceEntityIdentifier, 'image_attribute', 30, false, false);
        $this->createOptionAttribute((string) $referenceEntityIdentifier, 'option_attribute', ['blue', 'red'], 40, false, false);
        $this->createOptionCollectionAttribute((string) $referenceEntityIdentifier, 'option_collection_attribute', ['blue', 'red'], 50, false, false);
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
            AttributeRegularExpression::createEmpty(),
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

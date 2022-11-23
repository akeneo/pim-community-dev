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
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class FindReferenceEntityDetailsTest extends SqlIntegrationTestCase
{
    private FindReferenceEntityDetailsInterface $findReferenceEntityDetails;

    public function setUp(): void
    {
        parent::setUp();

        $this->findReferenceEntityDetails = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.query.enrich.find_reference_entity_details_public_api'
        );
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntityWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_reference_entity_details(): void
    {
        $referenceEntityDetails = $this->findReferenceEntityDetails->findByCode('designer');

        $expectedNormalizedDetails = [
            'code' => 'designer',
            'labels' => [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
            'record_count' => 0,
            'attributes' => [
                [
                    'code' => 'label',
                    'reference_entity_code' => 'designer',
                    'type' => 'text',
                    'labels' => [],
                    'is_required' => false,
                    'value_per_locale' => true,
                    'value_per_channel' => false,
                    'order' => 0,
                ],
                [
                    'code' => 'image',
                    'reference_entity_code' => 'designer',
                    'type' => 'image',
                    'labels' => [],
                    'is_required' => false,
                    'value_per_locale' => false,
                    'value_per_channel' => false,
                    'order' => 1,
                ],
                [
                    'code' => 'text_attribute',
                    'reference_entity_code' => 'designer',
                    'type' => 'text',
                    'labels' => [],
                    'is_required' => true,
                    'value_per_locale' => true,
                    'value_per_channel' => true,
                    'order' => 10,
                ],
                [
                    'code' => 'number_attribute',
                    'reference_entity_code' => 'designer',
                    'type' => 'number',
                    'labels' => [],
                    'is_required' => true,
                    'value_per_locale' => false,
                    'value_per_channel' => false,
                    'order' => 20,
                ],
                [
                    'code' => 'image_attribute',
                    'reference_entity_code' => 'designer',
                    'type' => 'image',
                    'labels' => [],
                    'is_required' => true,
                    'value_per_locale' => false,
                    'value_per_channel' => false,
                    'order' => 30,
                ],
                [
                    'code' => 'option_attribute',
                    'reference_entity_code' => 'designer',
                    'type' => 'option',
                    'labels' => [],
                    'is_required' => true,
                    'value_per_locale' => false,
                    'value_per_channel' => false,
                    'order' => 40,
                ],
                [
                    'code' => 'option_collection_attribute',
                    'reference_entity_code' => 'designer',
                    'type' => 'option_collection',
                    'labels' => [],
                    'is_required' => true,
                    'value_per_locale' => false,
                    'value_per_channel' => false,
                    'order' => 50,
                ],
            ],
        ];

        Assert::assertEquals($expectedNormalizedDetails, $referenceEntityDetails->normalize());
    }

    /**
     * @test
     */
    public function it_returns_null_when_not_found(): void
    {
        $referenceEntityDetails = $this->findReferenceEntityDetails->findByCode('unknown');

        Assert::assertNull($referenceEntityDetails);
    }

    private function loadReferenceEntityWithAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

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

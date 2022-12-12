<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Source\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\ReferenceEntity\ReferenceEntitySelectionConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Integration\Configuration;

class ReferenceEntitySelectionValidatorTest extends AbstractValidationTest
{
    private ?AttributeRepositoryInterface $attributeRepository = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->get('feature_flags')->enable('reference_entity');
        $this->loadReferenceEntity();
    }

    /**
     * @dataProvider validSelection
     */
    public function test_it_does_not_build_violations_on_valid_selection(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ReferenceEntitySelectionConstraint());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSelection
     */
    public function test_it_builds_violations_on_invalid_selection(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new ReferenceEntitySelectionConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSelection(): array
    {
        return [
            'a valid code selection' => [
                [
                    'type' => 'code',
                ],
            ],
            'a valid "description" text attribute selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'text_attribute_designer_fingerprint',
                    'attribute_type' => 'text',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => 'en_US',
                ],
            ],
            'a valid "name" text attribute selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'another_one_designer_fingerprint',
                    'attribute_type' => 'text',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                ],
            ],
            'a valid "size" number attribute selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'size_designer_fingerprint',
                    'attribute_type' => 'number',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                    'decimal_separator' => ',',
                ],
            ],
            'a valid "tags" option attribute code selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'tags_designer_fingerprint',
                    'attribute_type' => 'option',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                    'option_selection' => [
                        'type' => 'code',
                    ]
                ],
            ],
            'a valid "tags" option attribute label selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'tags_designer_fingerprint',
                    'attribute_type' => 'option',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                    'option_selection' => [
                        'type' => 'label',
                        'locale' => 'en_US',
                    ]
                ],
            ],
            'a valid "collection" option collection attribute code selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'collection_designer_fingerprint',
                    'attribute_type' => 'option_collection',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                    'option_selection' => [
                        'type' => 'code',
                        'locale' => 'en_US',
                    ]
                ],
            ],
            'a valid "collection" option collection attribute label selection' => [
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'collection_designer_fingerprint',
                    'attribute_type' => 'option_collection',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                    'option_selection' => [
                        'type' => 'label',
                        'locale' => 'en_US',
                        'separator' => ',',
                    ]
                ],
            ],
        ];
    }

    public function invalidSelection(): array
    {
        return [
            'an invalid selection type' => [
                'The value you selected is not a valid choice.',
                '[type]',
                [
                    'type' => 'invalid_type',
                ],
            ],
            'an invalid selection property' => [
                'This field was not expected.',
                '[unknown_property]',
                [
                    'type' => 'code',
                    'unknown_property' => 'foo',
                ],
            ],
            'an attribute selection with deleted attribute' => [
                ReferenceEntitySelectionConstraint::ATTRIBUTE_NOT_FOUND,
                '[type]',
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'unknown_attribute',
                    'attribute_type' => 'text',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                ],
            ],
            'a number attribute with invalid decimal separator' => [
                'The value you selected is not a valid choice.',
                '[decimal_separator]',
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'unknown_attribute',
                    'attribute_type' => 'text',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                    'decimal_separator' => 'o',
                ],
            ],
            'a unsupported attribute type' => [
                'The value you selected is not a valid choice.',
                '[attribute_type]',
                [
                    'type' => 'attribute',
                    'attribute_identifier' => 'unknown_attribute',
                    'attribute_type' => 'image',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
                ],
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create($referenceEntityIdentifier, [], Image::createEmpty());
        $referenceEntityRepository->create($referenceEntity);
        $this->createTextAttribute((string) $referenceEntityIdentifier, 'text_attribute', 10, false, true);
        $this->createTextAttribute((string) $referenceEntityIdentifier, 'another_one', 20, false, false);
        $this->createNumberAttribute((string) $referenceEntityIdentifier, 'size', 30, false, false);
        $this->createOptionAttribute((string) $referenceEntityIdentifier, 'tags', ['red', 'blue'], 40, false, false);
        $this->createOptionCollectionAttribute((string) $referenceEntityIdentifier, 'collection', ['spring', 'summer'], 50, false, false);
    }

    private function createTextAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
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
        $this->attributeRepository->create($attribute);
    }

    private function createNumberAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
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
        $this->attributeRepository->create($attribute);
    }

    private function createOptionAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        array $options,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
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
                LabelCollection::fromArray([
                    'en_US' => sprintf('%s in english', $optionCode),
                    'fr_FR' => sprintf('%s en français', $optionCode),
                ]),
            ),
            $options,
        ));
        $this->attributeRepository->create($attribute);
    }

    private function createOptionCollectionAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        array $options,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
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
        $this->attributeRepository->create($attribute);
    }
}

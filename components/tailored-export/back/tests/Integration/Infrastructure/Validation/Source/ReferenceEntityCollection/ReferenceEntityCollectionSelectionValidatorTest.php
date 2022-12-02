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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Source\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\ReferenceEntityCollection\ReferenceEntityCollectionSelectionConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\Configuration;

class ReferenceEntityCollectionSelectionValidatorTest extends AbstractValidationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('feature_flags')->enable('reference_entity');
        $this->loadReferenceEntity();
    }

    /**
     * @dataProvider validSelection
     */
    public function test_it_does_not_build_violations_on_valid_selection(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new ReferenceEntityCollectionSelectionConstraint());

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
        $violations = $this->getValidator()->validate($value, new ReferenceEntityCollectionSelectionConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSelection(): array
    {
        return [
            'a valid code selection' => [
                [
                    'type' => 'code',
                    'separator' => ',',
                ],
            ],
            'a valid "description" attribute selection' => [
                [
                    'type' => 'attribute',
                    'separator' => ';',
                    'attribute_identifier' => 'text_attribute_designer_fingerprint',
                    'attribute_type' => 'text',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => 'en_US',
                ],
            ],
            'a valid "name" attribute selection' => [
                [
                    'type' => 'attribute',
                    'separator' => '|',
                    'attribute_identifier' => 'another_one_designer_fingerprint',
                    'attribute_type' => 'text',
                    'reference_entity_code' => 'designer',
                    'channel' => null,
                    'locale' => null,
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
                    'separator' => ',',
                ],
            ],
            'an invalid selection property' => [
                'This field was not expected.',
                '[unknown_property]',
                [
                    'type' => 'code',
                    'separator' => ',',
                    'unknown_property' => 'foo',
                ],
            ],
            'an invalid separator' => [
                'The value you selected is not a valid choice.',
                '[separator]',
                [
                    'type' => 'code',
                    'separator' => 'TWO',
                ],
            ],
            'an attribute selection with deleted attribute' => [
                ReferenceEntityCollectionSelectionConstraint::ATTRIBUTE_NOT_FOUND,
                '[type]',
                [
                    'type' => 'attribute',
                    'separator' => ',',
                    'attribute_identifier' => 'unknown_attribute',
                    'attribute_type' => 'text',
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
    }

    private function createTextAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
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
    }
}

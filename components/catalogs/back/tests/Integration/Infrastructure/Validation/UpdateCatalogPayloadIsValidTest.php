<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload
 */
class UpdateCatalogPayloadIsValidTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testItValidates(): void
    {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['familyA', 'familyB'],
                ],
                [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 80,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
        ], new CatalogUpdatePayload());

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWithMissingValues(): void
    {
        $violations = $this->validator->validate([], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, 'This field is missing.');
    }

    public function testItReturnsViolationsWhenProductSelectionCriteriaIsAssociativeArray(): void
    {
        $violations = $this->validator->validate([
            'enabled' => true,
            'product_selection_criteria' => [
                'foo' => [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    /**
     * @dataProvider invalidFieldDataProvider
     * @dataProvider invalidEnabledCriterionDataProvider
     * @dataProvider invalidFamilyCriterionDataProvider
     * @dataProvider invalidCompletenessCriterionDataProvider
     */
    public function testItReturnsViolationsWhenProductSelectionCriterionIsInvalid(
        array $criterion,
        string $expectedMessage
    ): void {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [$criterion],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidFieldDataProvider(): array
    {
        return [
            'invalid field value' => [
                'criterion' => [
                    'field' => 'some_random_field',
                    'operator' => '<=',
                    'value' => false,
                ],
                'expectedMessage' => 'Invalid field value',
            ],
        ];
    }

    public function invalidEnabledCriterionDataProvider(): array
    {
        return [
            'enabled field with invalid operator' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '<=',
                    'value' => false,
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'enabled field with invalid value' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => 56,
                ],
                'expectedMessage' => 'This value should be of type boolean.',
            ],
        ];
    }

    public function invalidFamilyCriterionDataProvider(): array
    {
        return [
            'family field with invalid operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => '>',
                    'value' => ['familyA', 'familyB'],
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'family field with invalid value' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['familyA', 2, 'familyB'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }

    public function invalidCompletenessCriterionDataProvider(): array
    {
        return [
            'completeness field with invalid operator' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => 'IN',
                    'value' => 42,
                    'scope' => 'print',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'completeness field with invalid value' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 420,
                    'scope' => 'print',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'Completeness value must be between 0 and 100 percent.',
            ],
            'completeness field with invalid channel' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 100,
                    'scope' => 32,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'completeness field with invalid locale' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 100,
                    'scope' => 'print',
                    'locale' => false,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'completeness field with non existent channel' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '=',
                    'value' => 23,
                    'scope' => 'print',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'completeness field with invalid locale for a channel' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '=',
                    'value' => 23,
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
        ];
    }
}

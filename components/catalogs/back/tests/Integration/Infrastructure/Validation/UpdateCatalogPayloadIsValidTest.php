<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\UpdateCatalogPayloadIsValid;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\UpdateCatalogPayloadIsValid
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
        ], new UpdateCatalogPayloadIsValid());

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWithMissingValues(): void
    {
        $violations = $this->validator->validate([], new UpdateCatalogPayloadIsValid());

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
        ], new UpdateCatalogPayloadIsValid());

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    /**
     * @dataProvider invalidCriterionDataProvider
     */
    public function testItReturnsViolationsWhenProductSelectionCriterionIsInvalid(
        array $criterion,
        string $expectedMessage,
        string $expectedPath
    ): void {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [$criterion],
        ], new UpdateCatalogPayloadIsValid());

        $this->assertViolationsListContains($violations, $expectedMessage, $expectedPath);
    }

    public function invalidCriterionDataProvider(): array
    {
        return [
            'invalid field value' => [
                'criterion' => [
                    'field' => 'some_random_field',
                    'operator' => '<=',
                    'value' => false,
                ],
                'expectedMessage' => 'Invalid field value',
                'expectedPath' => '[product_selection_criteria][0][field]',
            ],
            'enabled field with invalid operator' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '<=',
                    'value' => false,
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
                'expectedPath' => '[product_selection_criteria][0][operator]',
            ],
            'enabled field with invalid value' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => 56,
                ],
                'expectedMessage' => 'This value should be of type boolean.',
                'expectedPath' => '[product_selection_criteria][0][value]',
            ],
            'family field with invalid operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => '>',
                    'value' => ['familyA', 'familyB'],
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
                'expectedPath' => '[product_selection_criteria][0][operator]',
            ],
            'family field with invalid value' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['familyA', 2, 'familyB'],
                ],
                'expectedMessage' => 'This value should be of type string.',
                'expectedPath' => '[product_selection_criteria][0][value][1]',
            ],
            'completeness field with invalid operator' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => 'IN',
                    'value' => 42,
                    'scope' => 'print',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
                'expectedPath' => '[product_selection_criteria][0][operator]',
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
                'expectedPath' => '[product_selection_criteria][0][value]',
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
                'expectedPath' => '[product_selection_criteria][0][scope]',
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
                'expectedPath' => '[product_selection_criteria][0][locale]',
            ],
            'completeness field with non existent channel' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '=',
                    'value' => 23,
                    'scope' => 'print',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'Invalid channel value.',
                'expectedPath' => '[product_selection_criteria][0][scope]',
            ],
            'completeness field with invalid locale for a channel' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '=',
                    'value' => 23,
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'Locale does not exist or not activated for the channel.',
                'expectedPath' => '[product_selection_criteria][0][locale]',
            ],
        ];
    }
}

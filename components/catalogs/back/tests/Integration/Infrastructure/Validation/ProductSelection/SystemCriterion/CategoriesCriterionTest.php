<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\CategoriesCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\CategoriesCriterion
 */
class CategoriesCriterionTest extends AbstractSystemCriterionTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createCategory(['code' => 'categoryA', 'label' => '[categoryA]']);
        $this->createCategory(['code' => 'categoryB', 'label' => '[categoryB]']);
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $criterion): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new CategoriesCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'field with IN_LIST operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['categoryA', 'categoryB'],
                ],
            ],
            'field with NOT_IN_LIST operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'NOT IN',
                    'value' => ['categoryA', 'categoryB'],
                ],
            ],
            'field with IN_CHILDREN_LIST operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN CHILDREN',
                    'value' => ['categoryA', 'categoryB'],
                ],
            ],
            'field with NOT_IN_CHILDREN_LIST operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'NOT IN CHILDREN',
                    'value' => ['categoryA', 'categoryB'],
                ],
            ],
            'field with UNCLASSIFIED operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'UNCLASSIFIED',
                    'value' => [],
                ],
            ],
            'field with IN_LIST_OR_UNCLASSIFIED operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN OR UNCLASSIFIED',
                    'value' => ['categoryA', 'categoryB'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(array $criterion, string $expectedMessage): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new CategoriesCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'category field with invalid field' => [
                'criterion' => [
                    'field' => 'foo',
                    'operator' => 'IN',
                    'value' => ['categoryA', 'categoryB'],
                ],
                'expectedMessage' => 'This value should be identical to string "categories".',
            ],
            'category field with invalid operator' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => '>',
                    'value' => ['categoryA'],
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'category field with invalid value' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => 123,
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'category field with missing value' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'category field with extra field' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['categoryA'],
                    'locale' => 'fr_FR',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'category field with non empty value while operator is unclassified' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'UNCLASSIFIED',
                    'value' => ['categoryA'],
                ],
                'expectedMessage' => 'This value must be empty.',
            ],
            'category field requires non empty value ' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN CHILDREN',
                    'value' => [],
                ],
                'expectedMessage' => 'This value must not be empty.',
            ],
            'category field value contains an item with bad value type' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['categoryA', 432],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'category field value contains an item with unknown category code' => [
                'criterion' => [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['categoryB', 'unknown_code_1', 'categoryA', 'unknown_code_2'],
                ],
                'expectedMessage' => 'The following categories do not exist anymore: unknown_code_1, unknown_code_2. Please remove them from the criterion value.',
            ],
        ];
    }
}

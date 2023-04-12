<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\CompletenessCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\CompletenessCriterion
 */
class CompletenessCriterionTest extends AbstractSystemCriterionTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $criterion): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new CompletenessCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'field with EQUALS operator' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '=',
                    'value' => 80,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'field with NOT_EQUAL operator' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '!=',
                    'value' => 99,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'field with LOWER_THAN operator' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '<',
                    'value' => 56,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'field with GREATER_THAN operator' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 12,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(array $criterion, string $expectedMessage): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new CompletenessCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'completeness field with invalid field' => [
                'criterion' => [
                    'field' => 'complete',
                    'operator' => 'IN',
                    'value' => 42,
                    'scope' => 'print',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be identical to string "completeness".',
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
            'completeness field with invalid scope type' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 100,
                    'scope' => 32,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'completeness field with blank scope' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 100,
                    'scope' => '',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'completeness field with invalid locale type' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 100,
                    'scope' => 'print',
                    'locale' => false,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'completeness field with blank locale' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 100,
                    'scope' => 'print',
                    'locale' => '',
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'completeness field with missing scope' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '!=',
                    'value' => 99,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'completeness field with extra field' => [
                'criterion' => [
                    'field' => 'completeness',
                    'operator' => '!=',
                    'value' => 99,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'foo' => 'bar',
                ],
                'expectedMessage' => 'This field was not expected.',
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

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\EnabledCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\EnabledCriterion
 */
class EnabledCriterionTest extends AbstractSystemCriterionTest
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $criterion): void
    {
        $violations = $this->validator->validate($criterion, new EnabledCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'field with EQUALS operator' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            'field with NOT_EQUAL operator' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '!=',
                    'value' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(array $criterion, string $expectedMessage): void
    {
        $violations = $this->validator->validate($criterion, new EnabledCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'enabled field with invalid field' => [
                'criterion' => [
                    'field' => 'active',
                    'operator' => '=',
                    'value' => false,
                ],
                'expectedMessage' => 'This value should be identical to string "enabled".',
            ],
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
            'enabled field with missing value' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '=',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'enabled field with extra field' => [
                'criterion' => [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                    'scope' => 'print',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}

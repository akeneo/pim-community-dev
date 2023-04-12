<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\FamilyCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\SystemCriterion\FamilyCriterion
 */
class FamilyCriterionTest extends AbstractSystemCriterionTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily(['code' => 'familyA', 'label' => '[familyA]']);
        $this->createFamily(['code' => 'familyB', 'label' => '[familyB]']);
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $criterion): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new FamilyCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'field with EMPTY operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'EMPTY',
                    'value' => [],
                ],
            ],
            'field with NOT EMPTY operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'NOT EMPTY',
                    'value' => [],
                ],
            ],
            'field with IN operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['familyA', 'familyB'],
                ],
            ],
            'field with NOT IN operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'NOT IN',
                    'value' => ['familyA', 'familyB'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(array $criterion, string $expectedMessage): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new FamilyCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'family field with invalid field' => [
                'criterion' => [
                    'field' => 'families',
                    'operator' => 'IN',
                    'value' => ['familyA', 'familyB'],
                ],
                'expectedMessage' => 'This value should be identical to string "family".',
            ],
            'family field with invalid operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => '>',
                    'value' => ['familyA', 'familyB'],
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'family field with invalid value type' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => false,
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'family field with invalid value' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['familyA', 2, 'familyB'],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'family field with non empty values with empty operator' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'EMPTY',
                    'value' => ['familyA', 'familyB'],
                ],
                'expectedMessage' => 'This value must be empty.',
            ],
            'family field with missing value' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'EMPTY',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'family field with extra field' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'EMPTY',
                    'value' => ['familyA', 'familyB'],
                    'locale' => 'fr_FR',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'family field with missing or unknown family code' => [
                'criterion' => [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => ['familyA', 'unknown', 'familyB'],
                ],
                'expectedMessage' => 'At least one selected family does not exist.',
            ],
        ];
    }
}

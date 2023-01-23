<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeNumberCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNumberCriterionTest extends AbstractAttributeCriterionTest
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validDataProvider
     * @dataProvider validOperatorsDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($criterion, new AttributeNumberCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '=',
                    'value' => 4,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '=',
                    'value' => 4,
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '=',
                    'value' => 4,
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '=',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
    }

    public function validOperatorsDataProvider(): array
    {
        return [
            'field with EQUALS operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '=',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_EQUAL operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '!=',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with LOWER_THAN operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '<',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with LOWER_OR_EQUAL_THAN operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '<=',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with GREATER_THAN operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '>',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with GREATER_OR_EQUAL_THAN operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '>=',
                    'value' => 4,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_EMPTY operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_NOT_EMPTY operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'NOT EMPTY',
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(
        array $attribute,
        array $criterion,
        string $expectedMessage,
    ): void {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($criterion, new AttributeNumberCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'forbidden extra field' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'NOT EMPTY',
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                    'foo' => 'bar',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'missing field' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid field value' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 42,
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'foo',
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => '=',
                    'value' => 'foo',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type numeric.',
            ],
            'invalid scope format' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => 42,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale format' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => null,
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope value' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'invalid locale value' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => null,
                    'locale' => 'jp_JP',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'invalid scoped locale value' => [
                'attribute' => [
                    'code' => 'number_battery_cells',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'number_battery_cells',
                    'operator' => 'EMPTY',
                    'value' => null,
                    'scope' => 'ecommerce',
                    'locale' => 'jp_JP',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
        ];
    }
}

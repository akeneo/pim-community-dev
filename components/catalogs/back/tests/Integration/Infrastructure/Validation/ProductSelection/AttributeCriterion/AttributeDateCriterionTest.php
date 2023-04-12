<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeDateCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDateCriterionTest extends AbstractAttributeCriterionTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validDataProvider
     * @dataProvider validOperatorsDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new AttributeDateCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
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
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '=',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_EQUAL operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '!=',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with LOWER_THAN operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '<',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with GREATER_THAN operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with BETWEEN operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'BETWEEN',
                    'value' => ['2021-12-31', '2022-12-31'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_BETWEEN operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'NOT BETWEEN',
                    'value' => ['2021-12-31', '2022-12-31'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_EMPTY operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'EMPTY',
                    'value' => '',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_NOT_EMPTY operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'NOT EMPTY',
                    'value' => '',
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

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new AttributeDateCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'forbidden extra field' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                    'foo' => 'bar',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'missing field' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid field value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 42,
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'IN CHILDREN',
                    'value' => '2021-12-31',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value type' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid value type in array' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'BETWEEN',
                    'value' => [42, 42],
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid value date format' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value is not a valid date.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 42,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid scope' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid locale' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'field with EQUALS operator has an array value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '=',
                    'value' => ['2021-12-31'],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with NOT_EQUAL operator has an array value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '!=',
                    'value' => ['2021-12-31'],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with LOWER_THAN operator has an array value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '<',
                    'value' => ['2021-12-31'],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with GREATER_THAN operator has an array value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => '>',
                    'value' => ['2021-12-31'],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with BETWEEN operator has a non array value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'BETWEEN',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'field with NOT_BETWEEN operator has a non array value' => [
                'attribute' => [
                    'code' => 'released_at',
                    'type' => 'pim_catalog_date',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'released_at',
                    'operator' => 'NOT BETWEEN',
                    'value' => '2021-12-31',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
        ];
    }
}

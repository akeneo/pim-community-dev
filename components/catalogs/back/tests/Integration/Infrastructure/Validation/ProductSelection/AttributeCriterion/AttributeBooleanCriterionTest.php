<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeBooleanCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeBooleanCriterionTest extends AbstractAttributeCriterionTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new AttributeBooleanCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => true,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => true,
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => true,
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => true,
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

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new AttributeBooleanCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'invalid field value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 42,
                    'operator' => '=',
                    'value' => true,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => 'IN',
                    'value' => true,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type boolean.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => true,
                    'scope' => 42,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => false,
                    'scope' => 'ecommerce',
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => false,
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => false,
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => false,
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'field with EMPTY operator has a non empty value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_boolean',
                    'group' => 'other',
                    'scopable' => 'ecommerce',
                    'localizable' => 'en_US',
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => 'EMPTY',
                    'value' => false,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value must be empty.',
            ],
        ];
    }
}

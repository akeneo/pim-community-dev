<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeTextareaCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTextareaCriterionTest extends AbstractAttributeCriterionTest
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

        $violations = $this->validator->validate($criterion, new AttributeTextareaCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
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
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_EQUAL operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '!=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with CONTAINS operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'CONTAINS',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with DOES_NOT_CONTAIN operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'DOES NOT CONTAIN',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with STARTS_WITH operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'STARTS WITH',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_EMPTY operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'EMPTY',
                    'value' => '',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_NOT_EMPTY operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
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
        string $expectedMessage
    ): void {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($criterion, new AttributeTextareaCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'forbidden extra field' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'NOT EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                    'foo' => 'bar',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'missing field' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid field value' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 42,
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'IN',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 42,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid scope' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid locale' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'field with EMPTY operator has a non empty value' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'EMPTY',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value must be empty.',
            ],
            'field with CONTAINS operator has an empty value' => [
                'attribute' => [
                    'code' => 'description',
                    'type' => 'pim_catalog_textarea',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'description',
                    'operator' => 'CONTAINS',
                    'value' => '',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value must not be empty.',
            ],
        ];
    }
}

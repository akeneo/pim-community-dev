<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeSimpleSelectCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSimpleSelectCriterionTest extends AbstractAttributeCriterionTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validScopeAndLocaleDataProvider
     * @dataProvider validOperatorsDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new AttributeSimpleSelectCriterion());

        $this->assertEmpty($violations);
    }

    public function validScopeAndLocaleDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
    }

    public function validOperatorsDataProvider(): array
    {
        return [
            'field with IN_LIST operator' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'IN',
                    'value' => ['L', 'XL'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_IN_LIST operator' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'NOT IN',
                    'value' => ['L', 'XL'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_EMPTY operator' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_NOT_EMPTY operator' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'NOT EMPTY',
                    'value' => [],
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

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($criterion, new AttributeSimpleSelectCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'forbidden extra field' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
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
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid field value' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 42,
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => '=',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => '',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'invalid code format in value' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [42],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope format' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 42,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale format' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope value' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'invalid locale value' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => 'jp_JP',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'invalid scoped locale value' => [
                'attribute' => [
                    'code' => 'clothing_size',
                    'type' => 'pim_catalog_simpleselect',
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'clothing_size',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'ecommerce',
                    'locale' => 'jp_JP',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
        ];
    }
}

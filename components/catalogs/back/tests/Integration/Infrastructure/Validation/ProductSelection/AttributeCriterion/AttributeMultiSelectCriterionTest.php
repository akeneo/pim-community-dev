<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeMultiSelectCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMultiSelectCriterionTest extends AbstractAttributeCriterionTest
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validScopeAndLocaleDataProvider
     * @dataProvider validOperatorsDataProvider
     */
    public function testItReturnsNoViolation(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($criterion, new AttributeMultiSelectCriterion());

        $this->assertEmpty($violations);
    }

    public function validScopeAndLocaleDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
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
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'IN',
                    'value' => ['Cotton', 'Wool'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_IN_LIST operator' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'NOT IN',
                    'value' => ['Cotton', 'Wool'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_EMPTY operator' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_NOT_EMPTY operator' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
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

        $violations = $this->validator->validate($criterion, new AttributeMultiSelectCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'forbidden extra field' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
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
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid field value' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
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
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => '=',
                    'value' => [],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => '',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'invalid code format in value' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [42],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope format' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 42,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale format' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope value' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'invalid locale value' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'materials',
                    'operator' => 'EMPTY',
                    'value' => [],
                    'scope' => null,
                    'locale' => 'jp_JP',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'invalid scoped locale value' => [
                'attribute' => [
                    'code' => 'materials',
                    'type' => 'pim_catalog_multiselect',
                    'options' => ['Cotton', 'Leather', 'Polyester', 'Wool'],
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'materials',
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

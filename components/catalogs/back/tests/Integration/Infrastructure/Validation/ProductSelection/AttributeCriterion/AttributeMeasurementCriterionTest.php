<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeMeasurementCriterion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMeasurementCriterionTest extends AbstractAttributeCriterionTest
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

        $this->createMeasurementsFamily($attribute);

        $violations = $this->validator->validate($criterion, new AttributeMeasurementCriterion());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'Weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'Weight',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => 'en_US'
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
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
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with NOT_EQUAL operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::NOT_EQUAL,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with LOWER_THAN operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::LOWER_THAN,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with LOWER_OR_EQUAL_THAN operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::LOWER_OR_EQUAL_THAN,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with GREATER_THAN operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::GREATER_THAN,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with GREATER_OR_EQUAL_THAN operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::GREATER_OR_EQUAL_THAN,
                    'value' => [
                        'amount' => 12.3,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_EMPTY operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::IS_EMPTY,
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'field with IS_NOT_EMPTY operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::IS_NOT_EMPTY,
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
        string $expectedMessage
    ): void {
        $this->createAttribute($attribute);

        $this->createMeasurementsFamily($attribute);

        $violations = $this->validator->validate($criterion, new AttributeMeasurementCriterion());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'invalid field value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 42,
                    'operator' => Operator::IS_NOT_EMPTY,
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::IN_LIST,
                    'value' => null,
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => 42,
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type array|(Traversable&ArrayAccess).',
            ],
            'invalid unit' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'KILOGRAM',
                            'label' => 'Kilogram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'invalid_measurement_code',
                    ],
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'The unit of the field "name" does not exist.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 42,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 'ecommerce',
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'field with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::EQUALS,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'GRAM'
                    ],
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'field with EMPTY operator has a non empty value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'units' => [
                        [
                            'code' => 'GRAM',
                            'label' => 'Gram',
                        ],
                    ],
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => Operator::IS_EMPTY,
                    'value' => [
                        'amount' => 42,
                        'unit' => 'GRAM'
                    ],
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value must be empty.',
            ],
        ];
    }
}

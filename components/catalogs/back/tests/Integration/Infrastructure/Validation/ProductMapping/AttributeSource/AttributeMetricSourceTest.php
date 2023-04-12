<?php

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeMetricSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeMetricSource
 */
class AttributeMetricSourceTest extends AbstractAttributeSourceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createMeasurementsFamily([
            'code' => 'Weight',
            'units' => [
                [
                    'code' => 'GRAM',
                    'label' => 'Gram',
                ],
                [
                    'code' => 'KILOGRAM',
                    'label' => 'Kilogram',
                ],
            ],
        ]);
    }

    /**
     * @dataProvider validDataProvider
     * @param array<array-key, array{source: string|null, scope:string|null, locale: string|null}> $source
     */
    public function testItReturnsNoViolation(array $attribute, array $source): void
    {
        $this->createAttribute($attribute);
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($source, new AttributeMetricSource());
        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidDataProvider
     * @param array<array-key, array{source: string|null, scope:string|null, locale: string|null}> $source
     */
    public function testItReturnsViolationsWhenInvalid(
        array $attribute,
        array $source,
        string $expectedMessage,
    ): void {
        $this->createAttribute($attribute);
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($source, new AttributeMetricSource());
        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'KILOGRAM',
                    ],
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'unit' => 'KILOGRAM',
                    ],
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'KILOGRAM',
                    ],
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
            ],
            'with default value' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'default' => 10,
                    'parameters' => [
                        'unit' => 'KILOGRAM',
                    ],
                ],
            ],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            'missing mandatory parameters field' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'payload with an extra field' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                    'foo' => 'bar',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'unknown extra parameter' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'GRAM',
                        'foo' => 'bar',
                    ],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'blank unit parameter' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => '',
                    ],
                ],
                'expectedMessage' => 'The unit must not be empty.',
            ],
            'null unit parameter' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => null,
                    ],
                ],
                'expectedMessage' => 'The unit must not be empty.',
            ],
            'invalid unit parameter' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 42,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing unit parameter' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'unknown unit parameter' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'UNKNOWN',
                    ],
                ],
                'expectedMessage' => 'The unit of the field "weight" does not exist.',
            ],
            'blank locale' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => '',
                    'parameters' => [
                        'currency' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                    'parameters' => [
                        'unit' => 'KILOGRAM',
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'missing locale value' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => null,
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'unknown locale' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'blank scope' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => '',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 42,
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing scope value' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'locale' => null,
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'unknown scope' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'blank source value' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => '',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid source value' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing source value' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'unit' => 'GRAM',
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'source with invalid default value type' => [
                'attribute' => [
                    'code' => 'weight',
                    'type' => 'pim_catalog_metric',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'measurement_family' => 'Weight',
                ],
                'source' => [
                    'source' => 'weight',
                    'scope' => null,
                    'locale' => null,
                    'default' => true,
                ],
                'expectedMessage' => 'This value should be of type numeric.',
            ],
        ];
    }
}

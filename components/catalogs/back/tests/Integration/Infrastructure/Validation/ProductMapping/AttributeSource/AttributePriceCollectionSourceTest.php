<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributePriceCollectionSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributePriceCollectionSourceTest extends AbstractAttributeSourceTest
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
    public function testItReturnsNoViolation(array $attribute, array $source): void
    {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($source, new AttributePriceCollectionSource());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'currency' => 'EUR',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(
        array $attribute,
        array $source,
        string $expectedMessage,
    ): void {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($source, new AttributePriceCollectionSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'missing mandatory parameters field' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
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
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                    'foo' => 'bar',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'unknown extra parameter' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                        'foo' => 'bar',
                    ],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'blank currency parameter' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => null,
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid currency parameter' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 42,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing currency parameter' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'unknown currency parameter' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'UNKNOWN',
                    ],
                ],
                'expectedMessage' => 'This currency is not activated. Please check your channels and currency settings or update this value.',
            ],
            'not scopable source with disabled currency' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'currency' => 'GBP',
                    ],
                ],
                'expectedMessage' => 'This currency is not activated. Please check your channels and currency settings or update this value.',
            ],
            'scopable source with invalid channel currency' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'currency' => 'EUR',
                    ],
                ],
                'expectedMessage' => 'This currency is not activated. Please check your channels and currency settings or update this value.',
            ],
            'blank locale' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => '',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'missing locale value' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'unknown locale' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'blank scope' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => '',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 42,
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing scope value' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'price',
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'unknown scope' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'price',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'blank source value' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => '',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid source value' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'currency' => 'USD',
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'missing source value' => [
                'attribute' => [
                    'code' => 'price',
                    'type' => 'pim_catalog_price_collection',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
        ];
    }
}

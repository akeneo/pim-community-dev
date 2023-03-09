<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeTextareaSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeTextareaSource
 */
class AttributeTextareaSourceTest extends AbstractAttributeSourceTest
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

        $violations = $this->validator->validate($source, new AttributeTextareaSource());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
            ],
            'default value defined' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => 'Default name',
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

        $violations = $this->validator->validate($source, new AttributeTextareaSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'missing source value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid source value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 42,
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'blank scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => '',
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expected_message' => 'This value should not be blank.',
            ],
            'unknown scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'missing scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'name',
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'blank locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => null,
                    'locale' => '',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expected_message' => 'This value should not be blank.',
            ],
            'missing locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'disabled locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'disabled locale for a channel' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'default' => null,
                    ],
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'missing default field' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid default field' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => 10,
                    ],
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'extra field' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_textarea',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'name',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ],
                    'EXTRA_FIELD' => null,
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}

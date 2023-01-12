<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeSimpleSelectSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeSimpleSelectSource
 */
class AttributeSimpleSelectSourceTest extends AbstractAttributeSourceTest
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

        $violations = $this->validator->validate($source, new AttributeSimpleSelectSource());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
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
        string $expectedMessage
    ): void {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate($source, new AttributeSimpleSelectSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'invalid source value' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 42,
                    'locale' => 'en_US',
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'source with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'source with invalid scope' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'source with invalid locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ]
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'source with missing parameters' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'source with missing label_locale field' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                    ]
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'source with invalid label_locale type' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'label_locale' => 42,
                    ]
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'source with invalid label_locale locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_simpleselect',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                    'options' => ['XS', 'S', 'M', 'L', 'XL'],
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                    'parameters' => [
                        'label_locale' => 'kz_KZ',
                    ]
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeNumberSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeNumberSource
 */
class AttributeNumberSourceTest extends AbstractAttributeSourceTest
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validDataProvider
     * @param array<array-key, array{source: string|null, scope:string|null, locale: string|null}> $source
     */
    public function testItReturnsNoViolation(array $attribute, array $source): void
    {
        $this->createAttribute($attribute);
        $violations = $this->validator->validate($source, new AttributeNumberSource());
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
        $violations = $this->validator->validate($source, new AttributeNumberSource());
        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function validDataProvider(): array
    {
        return [
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'label' => 'Optical size',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'label' => 'Optical size',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'label' => 'Optical size',
                    'type' => 'pim_catalog_number',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'size',
                    'label' => 'Optical size',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            'missing source value' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid source value' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 42,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'blank scope' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => '',
                    'locale' => null,
                ],
                'expected_message' => 'This value should not be blank.',
            ],
            'unknown scope' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deleted. Please check your channel settings or update this value.',
            ],
            'missing scope' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'blank locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => '',
                ],
                'expected_message' => 'This value should not be blank.',
            ],
            'missing locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'disabled locale' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled or does not exist anymore. Please check your channels and locales settings.',
            ],
            'disabled locale for a channel' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled. Please check your channels and locales settings or update this value.',
            ],
            'extra field' => [
                'attribute' => [
                    'code' => 'size',
                    'type' => 'pim_catalog_number',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}

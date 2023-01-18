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
            'without scope and without locale' => [
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
            'with a scope' => [
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
            'with a locale' => [
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
            'with a locale and a scope' => [
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
            'with a blank scope' => [
                'attribute' => [
                    'code' => 'size',
                    'label' => 'Optical size',
                    'type' => 'pim_catalog_number',
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
            'with a blank locale' => [
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
                    'locale' => '',
                ],
                'expected_message' => 'This value should not be blank.',
            ],
            'with blank locale and scope' => [
                'attribute' => [
                    'code' => 'size',
                    'label' => 'Optical size',
                    'type' => 'pim_catalog_number',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'source' => [
                    'source' => 'size',
                    'scope' => '',
                    'locale' => '',
                ],
                'expected_message' => 'This value should not be blank.',
            ],
        ];
    }
}

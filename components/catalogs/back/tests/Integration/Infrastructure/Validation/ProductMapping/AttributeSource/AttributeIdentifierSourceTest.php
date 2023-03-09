<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeIdentifierSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeIdentifierSource
 */
class AttributeIdentifierSourceTest extends AbstractAttributeSourceTest
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testItReturnsNoViolation(): void
    {
        $source = [
            'source' => 'sku',
            'scope' => null,
            'locale' => null,
            'parameters' => [
                'default' => null,
            ]
        ];
        $violations = $this->validator->validate($source, new AttributeIdentifierSource());
        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(
        array $source,
        string $expectedMessage,
    ): void {
        $violations = $this->validator->validate($source, new AttributeIdentifierSource());
        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'with a scope' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => 'ecommerce',
                    'locale' => null,
                    'parameters' => [
                        'default' => null,
                    ]
                ],
                'expected_message' => 'This value should be null.',
            ],
            'with a locale' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => null,
                    ]
                ],
                'expected_message' => 'This value should be null.',
            ],
            'with a locale and a scope' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                    'parameters' => [
                        'default' => null,
                    ]
                ],
                'expected_message' => 'This value should be null.',
            ],
            'missing parameters field' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'missing default field' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                    ]
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid default field' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'default' => 10,
                    ]
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }
}

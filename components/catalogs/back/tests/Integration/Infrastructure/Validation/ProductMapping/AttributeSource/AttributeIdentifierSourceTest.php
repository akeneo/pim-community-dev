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

    /**
     * @dataProvider validDataProvider
     */
    public function testItReturnsNoViolation(
        array $source,
    ): void {
        $violations = $this->validator->validate($source, new AttributeIdentifierSource());
        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'without default value' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'with default value' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => null,
                    'default' => '123456789',
                ],
            ],
        ];
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
                ],
                'expected_message' => 'This value should be null.',
            ],
            'with a locale' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
                'expected_message' => 'This value should be null.',
            ],
            'with a locale and a scope' => [
                'source' => [
                    'source' => 'sku',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expected_message' => 'This value should be null.',
            ],
        ];
    }
}

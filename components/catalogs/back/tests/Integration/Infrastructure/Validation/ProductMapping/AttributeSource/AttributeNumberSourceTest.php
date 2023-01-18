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
    public function testItReturnsNoViolation(array $source): void
    {
        $violations = $this->validator->validate($source, new AttributeNumberSource());
        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidDataProvider
     * @param array<array-key, array{source: string|null, scope:string|null, locale: string|null}> $source
     */
    public function testItReturnsViolationsWhenInvalid(
        array $source,
        string $expectedMessage,
    ): void {
        $violations = $this->validator->validate($source, new AttributeNumberSource());
        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function validDataProvider(): array
    {
        return [
            'without scope and without locale' => [
                'source' => [
                    'source' => 'Optical size',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'with a scope' => [
                'source' => [
                    'source' => 'Optical size',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'with a locale' => [
                'source' => [
                    'source' => 'Optical size',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'with a locale and a scope' => [
                'source' => [
                    'source' => 'Optical size',
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
                'source' => [
                    'source' => 'Optical size',
                    'scope' => '',
                    'locale' => null,
                ],
                'expected_message' => 'This value should satisfy at least one of the following constraints: [1] This value should be null. [2] This value should not be blank.',
            ],
            'with a blank locale' => [
                'source' => [
                    'source' => 'Optical size',
                    'scope' => null,
                    'locale' => '',
                ],
                'expected_message' => 'This value should satisfy at least one of the following constraints: [1] This value should be null. [2] This value should not be blank.',
            ],
            'with blank locale and scope' => [
                'source' => [
                    'source' => 'Optical size',
                    'scope' => '',
                    'locale' => '',
                ],
                'expected_message' => 'This value should satisfy at least one of the following constraints: [1] This value should be null. [2] This value should not be blank.',
            ],
        ];
    }
}

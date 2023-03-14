<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\NullSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\NullSource\NullBooleanSource;
use Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource\AbstractAttributeSourceTest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\AttributeBooleanSource
 */
class NullBooleanSourceTest extends AbstractAttributeSourceTest
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
    public function testItReturnsNoViolation(array $source): void
    {
        $violations = $this->validator->validate($source, new NullBooleanSource());

        $this->assertEmpty($violations);
    }

    public function validDataProvider(): array
    {
        return [
            'without default value' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            'with default value set to true' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                    'default' => true,
                ],
            ],
            'with default value set to false' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                    'default' => false,
                ],
            ],
            'with default value set to null' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                    'default' => null,
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
        $violations = $this->validator->validate($source, new NullBooleanSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'missing source' => [
                'source' => [
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid source value' => [
                'source' => [
                    'source' => 'invalid',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'missing scope' => [
                'source' => [
                    'source' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid scope value' => [
                'source' => [
                    'source' => null,
                    'scope' => 'invalid',
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'missing locale' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'invalid locale' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => 'invalid',
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'invalid default string value' => [
                'source' => [
                    'source' => null,
                    'scope' => null,
                    'locale' => null,
                    'default' => 10,
                ],
                'expectedMessage' => 'This value should satisfy at least one of the following constraints: [1] This value should be of type boolean. [2] This value should be null.',
            ],
            'extra field' => [
                'source' => [
                    'source' => 'is_released',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}

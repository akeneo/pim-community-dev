<?php

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\SystemAttributeStatusSource;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\AttributeSource\SystemAttributeStatusSource
 */
class SystemAttributeStatusSourceTest extends AbstractAttributeSourceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testItReturnsNoViolation(): void
    {
        $source = [
            'source' => 'is_enabled',
            'scope' => null,
            'locale' => null,
        ];
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($source, new SystemAttributeStatusSource());

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(
        array $source,
        string $expectedMessage,
    ): void {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($source, new SystemAttributeStatusSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }


    public function invalidDataProvider(): array
    {
        return [
            'missing source value' => [
                'source' => [
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ],
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'blank source value' => [
                'source' => [
                    'source' => '',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [
                        'label_locale' => 'en_US',
                    ],
                ],
                'expectedMessage' => 'This value should not be blank.',
            ],
            'invalid source value' => [
                'source' => [
                    'source' => 50,
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'non null scope' => [
                'source' => [
                    'source' => 'is_enabled',
                    'scope' => 'e-commerce',
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'missing scope field' => [
                'source' => [
                    'source' => 'is_enabled',
                    'locale' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'missing locale' => [
                'source' => [
                    'source' => 'is_enabled',
                    'scope' => null,
                ],
                'expectedMessage' => 'This field is missing.',
            ],
            'non null locale' => [
                'source' => [
                    'source' => 'is_enabled',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'extra field' => [
                'source' => [
                    'source' => 'is_enabled',
                    'scope' => null,
                    'locale' => null,
                    'parameters' => [],
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
            'invalid default value' => [
                'source' => [
                    'source' => 'is_enabled',
                    'scope' => null,
                    'locale' => null,
                    'default' => 'false',
                ],
                'expectedMessage' => 'This value should be of type boolean.',
            ],
        ];
    }
}

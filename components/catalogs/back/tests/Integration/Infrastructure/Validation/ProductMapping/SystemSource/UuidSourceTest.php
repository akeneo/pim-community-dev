<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\SystemSource;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\SystemSource\UuidSource;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMapping\SystemSource\UuidSource
 */
class UuidSourceTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testItReturnsNoViolation(): void
    {
        $violations = $this->validator->validate(
            [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            new UuidSource(),
        );

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testItReturnsViolationsWhenInvalid(array $source, $expectedMessage): void
    {
        $violations = $this->validator->validate($source, new UuidSource());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidDataProvider(): array
    {
        return [
            'uuid source with invalid source' => [
                'source' => [
                    'source' => 'active',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be identical to string "uuid".',
            ],
            'uuid source with not null scope' => [
                'source' => [
                    'source' => 'uuid',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'uuid source with not null locale' => [
                'source' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be null.',
            ],
            'uuid source with extra field' => [
                'source' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                    'extraField' => '=',
                ],
                'expectedMessage' => 'This field was not expected.',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogProductValueFiltersValidator
 */
class CatalogProductValueFiltersTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidates(): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                [
                    'channels' => ['ecommerce'],
                    'locales' => ['en_US'],
                    'currencies' => ['EUR', 'USD'],
                ],
                [],
            ),
        );

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidProductValueFiltersProvider
     */
    public function testItReturnsViolationsWhenProductValueFiltersAreInvalid(
        array $filters,
        string $expectedMessage,
    ): void {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [],
                $filters,
                [],
            ),
        );

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidProductValueFiltersProvider(): array
    {
        return [
            'channel is not a valid array' => [
                'filters' => ['channels' => 'ecommerce'],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'channel does not exist' => [
                'filters' => ['channels' => ['removed_channel']],
                'expectedMessage' => 'The channel "removed_channel" has been deactivated. Please check your channel settings or remove this filter.',
            ],
            'locale is not a valid array' => [
                'filters' => ['locales' => 'en_US'],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'locale is not activated' => [
                'filters' => ['locales' => ['removed_locale']],
                'expectedMessage' => 'The locale "removed_locale" has been deactivated. Please check your locale settings or remove this filter.',
            ],
            'currency is not a valid array' => [
                'filters' => ['currencies' => 'USD'],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'currency is not activated' => [
                'filters' => ['currencies' => ['AUD']],
                'expectedMessage' => 'The currency "AUD" has been deactivated. Please check your currencies settings or remove this filter.',
            ],
        ];
    }
}

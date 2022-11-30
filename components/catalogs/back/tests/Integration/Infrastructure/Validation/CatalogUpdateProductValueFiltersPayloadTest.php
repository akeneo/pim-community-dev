<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdateProductValueFiltersPayload;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdateProductValueFiltersPayloadValidator
 */
class CatalogUpdateProductValueFiltersPayloadTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidates(): void
    {
        $violations = $this->validator->validate([
            'channels' => ['ecommerce'],
            'locales' => ['en_US'],
            'currencies' => ['EUR', 'USD'],
        ], new CatalogUpdateProductValueFiltersPayload());

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidProductValueFiltersProvider
     */
    public function testItReturnsViolationsWhenProductValueFiltersAreInvalid(
        array $filters,
        string $expectedMessage
    ): void {
        $violations = $this->validator->validate($filters, new CatalogUpdateProductValueFiltersPayload());

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

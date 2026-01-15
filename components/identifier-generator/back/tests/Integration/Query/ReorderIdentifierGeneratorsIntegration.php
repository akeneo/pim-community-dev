<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Integration\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\ReorderIdentifierGenerators;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderIdentifierGeneratorsIntegration extends TestCase
{
    /** @test */
    public function it_reorders_identifier_generators(): void
    {
        $this->getReorderQuery()->byCodes([
            IdentifierGeneratorCode::fromString('ig_b'),
            IdentifierGeneratorCode::fromString('ig_a'),
            IdentifierGeneratorCode::fromString('ig_c'),
        ]);
        $this->assertOrder(['ig_b', 'ig_a', 'ig_c']);
    }

    /** @test */
    public function it_reorders_missing_generators_at_the_end(): void
    {
        $this->getReorderQuery()->byCodes([
            IdentifierGeneratorCode::fromString('ig_c'),
            IdentifierGeneratorCode::fromString('ig_a'),
        ]);
        $this->assertOrder(['ig_c', 'ig_a', 'ig_b']);
    }

    /** @test */
    public function it_ignores_unknown_generator_codes(): void
    {
        $this->getReorderQuery()->byCodes([
            IdentifierGeneratorCode::fromString('ig_c'),
            IdentifierGeneratorCode::fromString('deleted_generator'),
            IdentifierGeneratorCode::fromString('ig_a'),
        ]);
        $this->assertOrder(['ig_c', 'ig_a', 'ig_b']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createIdentifierGenerator('ig_a');
        $this->createIdentifierGenerator('ig_b');
        $this->createIdentifierGenerator('ig_c');

        $this->assertOrder(['ig_a', 'ig_b', 'ig_c']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createIdentifierGenerator(?string $code = null): void
    {
        ($this->getCreateHandler())(new CreateGeneratorCommand(
            $code,
            [],
            [['type' => 'free_text', 'string' => 'akn']],
            [],
            'sku',
            null,
            'no'
        ));
    }

    private function assertOrder(array $expectedCodes): void
    {
        $orderedCodes = $this->getConnection()->executeQuery(
            'SELECT code FROM pim_catalog_identifier_generator ORDER BY sort_order ASC'
        )->fetchFirstColumn();

        Assert::assertSame($expectedCodes, $orderedCodes);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getReorderQuery(): ReorderIdentifierGenerators
    {
        return $this->get(ReorderIdentifierGenerators::class);
    }

    private function getCreateHandler(): CreateGeneratorHandler
    {
        return $this->get(CreateGeneratorHandler::class);
    }
}

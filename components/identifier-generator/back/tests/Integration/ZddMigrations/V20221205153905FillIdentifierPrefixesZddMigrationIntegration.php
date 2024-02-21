<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Integration\ZddMigrations;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\ZddMigrations\V20221205153905FillIdentifierPrefixesZddMigration;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class V20221205153905FillIdentifierPrefixesZddMigrationIntegration extends TestCase
{
    public function test_it_fills_identifier_prefixes_with_existing_products(): void
    {
        $uuid1 = $this->createProduct('AKN12', []);
        $this->createProduct('AKN-ABC', []);
        $uuid3 = $this->createProduct('SKU-456', []);

        $this->emptyPrefixTable();

        $uuid4 = $this->createProduct('existing_sku_12', []);

        $prefixesBeforeMigration = $this->getIdentifierGeneratorPrefixes();
        Assert::assertSame([
            [
                'uuid' => $uuid4,
                'prefix' => 'existing_sku_',
                'number' => '12',
            ],
            [
                'uuid' => $uuid4,
                'prefix' => 'existing_sku_1',
                'number' => '2',
            ],
        ], $prefixesBeforeMigration);

        $this->runMigration();

        $prefixesAfterMigration = $this->getIdentifierGeneratorPrefixes();
        Assert::assertEqualsCanonicalizing([
            [
                'uuid' => $uuid4,
                'prefix' => 'existing_sku_',
                'number' => '12',
            ],
            [
                'uuid' => $uuid4,
                'prefix' => 'existing_sku_1',
                'number' => '2',
            ],
            [
                'uuid' => $uuid1,
                'prefix' => 'AKN',
                'number' => '12',
            ],
            [
                'uuid' => $uuid1,
                'prefix' => 'AKN1',
                'number' => '2',
            ],
            [
                'uuid' => $uuid3,
                'prefix' => 'SKU-',
                'number' => '456',
            ],
            [
                'uuid' => $uuid3,
                'prefix' => 'SKU-4',
                'number' => '56',
            ],
            [
                'uuid' => $uuid3,
                'prefix' => 'SKU-45',
                'number' => '6',
            ],
        ], $prefixesAfterMigration);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function createProduct(string $identifier, array $userIntents): string
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo.pim.storage_utils.cache.cached_queries_clearer')->clear();

        return $this->getProductUuid($identifier)->toString();
    }

    private function getIdentifierGeneratorPrefixes(): array
    {
        $query = <<<SQL
            SELECT BIN_TO_UUID(product_uuid) as uuid, prefix, number FROM pim_catalog_identifier_generator_prefixes
        SQL;
        $stmt = $this->getConnection()->executeQuery($query);

        return $stmt->fetchAllAssociative();
    }

    private function emptyPrefixTable(): void
    {
        $query = <<<SQL
            DELETE FROM pim_catalog_identifier_generator_prefixes
        SQL;
        $this->getConnection()->executeQuery($query);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getMigration(): V20221205153905FillIdentifierPrefixesZddMigration
    {
        return $this->get(V20221205153905FillIdentifierPrefixesZddMigration::class);
    }

    private function runMigration(): void
    {
        $this->getMigration()->migrate();
    }
}

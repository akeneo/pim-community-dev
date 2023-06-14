<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20230525171223FillProductIdentifiersTableZddMigration;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class V20230525171223FillProductIdentifiersTableZddMigrationIntegration extends TestCase
{
    /** @test */
    public function it_throws_an_exception_if_the_new_table_does_not_exist(): void
    {
        $this->dropTable('pim_catalog_product_identifiers');
        $this->expectExceptionObject(
            new \RuntimeException(
                'The required "pim_catalog_product_identifiers" table have not been created yet'
            )
        );

        $this->runMigration();
    }

    /** @test */
    public function it_fills_the_new_identifiers_table(): void
    {
        $this->truncateIdentifiersTable();
        $this->runMigration();

        $expected = [];
        foreach ($this->getAttributeValues('sku') as $uuid => $sku) {
            $expected[$uuid] = null === $sku ? [] : [\sprintf('sku#%s', $sku)];
        }

        Assert::assertEqualsCanonicalizing(
            $expected,
            $this->getIdentifiersData()
        );
    }

    /** @test */
    public function it_fills_the_table_with_multiple_identifiers_and_handles_null_values(): void
    {
        $this->truncateIdentifiersTable();
        // TODO CPM-1066: Add a new identifier attribute instead
        $this->getConnection()->executeStatement(
            <<<SQL
            UPDATE pim_catalog_attribute SET attribute_type = 'pim_catalog_identifier' WHERE code = 'ean'
            SQL
        );
        $this->getConnection()->executeStatement(
            <<<SQL
            UPDATE pim_catalog_product
            SET raw_values = JSON_REMOVE(raw_values, '$.sku."<all_channels>"."<all_locales>"')
            ORDER BY rand()
            LIMIT 15
            SQL
        );
        $this->runMigration();

        $expected = [];
        foreach ($this->getAttributeValues('sku') as $uuid => $sku) {
            $expected[$uuid] = null === $sku ? [] : [\sprintf('sku#%s', $sku)];
        }
        foreach ($this->getAttributeValues('ean') as $uuid => $ean) {
            if (null !== $ean)
            $expected[$uuid][] = \sprintf('ean#%s', $ean);
            \sort($expected[$uuid]);
        }

        Assert::assertEqualsCanonicalizing(
            $expected,
            $this->getIdentifiersData()
        );
    }

    protected function tearDown(): void
    {
        $kernel = new \Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $consoleApp = new Application($kernel);
        $consoleApp->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '--full-database' => true,
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);

        $input = new ArrayInput([
            'command' => 'pim:installer:db',
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function runMigration(): void
    {
        $this->getMigration()->migrate();
    }

    private function dropTable(string $tableName): void
    {
        $this->getConnection()->executeStatement(\sprintf('DROP TABLE IF EXISTS %s', $tableName));
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getMigration(): V20230525171223FillProductIdentifiersTableZddMigration
    {
        return $this->get(V20230525171223FillProductIdentifiersTableZddMigration::class);
    }

    private function getIdentifiersData(): array
    {
        $data = $this->getConnection()->fetchAllKeyValue(
            <<<SQL
            SELECT BIN_TO_UUID(product_uuid) AS uuid, identifiers
            FROM pim_catalog_product_identifiers;
            SQL,
        );

        return \array_map('json_decode', $data);
    }

    public function getAttributeValues(string $attributeCode): array
    {
        return $this->getConnection()->fetchAllKeyValue(
            \sprintf(
                <<<SQL
                SELECT BIN_TO_UUID(uuid) AS uuid, raw_values->>'$.%s."<all_channels>"."<all_locales>"' AS value
                FROM pim_catalog_product;
                SQL,
                $attributeCode
            )
        );
    }

    private function truncateIdentifiersTable(): void
    {
        $this->getConnection()->executeStatement('TRUNCATE TABLE pim_catalog_product_identifiers');
    }
}

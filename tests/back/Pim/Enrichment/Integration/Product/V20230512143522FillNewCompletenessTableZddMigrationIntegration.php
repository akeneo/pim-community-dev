<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20230512143522FillNewCompletenessTableZddMigration;
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
final class V20230512143522FillNewCompletenessTableZddMigrationIntegration extends TestCase
{
    /** @test */
    public function it_throws_an_exception_if_the_new_table_does_not_exist(): void
    {
        $this->dropTable('pim_catalog_product_completeness');
        $this->expectExceptionObject(
            new \RuntimeException('The "pim_catalog_product_completeness" table does not exist yet')
        );

        $this->runMigration();
    }

    /** @test */
    public function it_fills_the_new_completeness_table(): void
    {
        $this->truncateCompletenessTable();
        Assert::assertEquals(0, $this->getCompletenessRowCount());
        $this->runMigration();
        Assert::assertEquals($this->getProductCount(), $this->getCompletenessRowCount());

        $newCompletenesses = $this->getNewCompletenesses();
        $legacyCompletenesses = $this->getLegacyCompletenesses();
        foreach ($legacyCompletenesses as $productUuid => $jsonCompleteness) {
            Assert::assertJsonStringEqualsJsonString(
                $newCompletenesses[$productUuid] ?? 'null',
                $jsonCompleteness
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->tableExists('pim_catalog_completeness')) {
            $msg = <<<EOL
                The legacy completeness table does not exist anymore, the migration should have already been performed.
                This test class does not make sense anymore, it should be deleted. 
                EOL;

            $this->markTestSkipped($msg);
        }
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

    private function getCompletenessRowCount(): int
    {
        return \intval(
            $this->getConnection()->fetchOne(
                'SELECT count(*) as nbLine FROM pim_catalog_product_completeness;'
            )
        );
    }

    private function getProductCount(): int
    {
        return \intval(
            $this->getConnection()->fetchOne(
                'SELECT count(DISTINCT product_uuid) as nb_products FROM pim_catalog_completeness;'
            )
        );
    }

    private function truncateCompletenessTable(): void
    {
        $this->getConnection()->executeStatement('TRUNCATE TABLE pim_catalog_product_completeness');
    }

    private function dropTable(string $tableName): void
    {
        $this->getConnection()->executeStatement(\sprintf('DROP TABLE IF EXISTS %s', $tableName));
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getMigration(): V20230512143522FillNewCompletenessTableZddMigration
    {
        return $this->get(V20230512143522FillNewCompletenessTableZddMigration::class);
    }

    private function getLegacyCompletenesses(): array
    {
        $completenesses = [];
        $rows = $this->getConnection()->iterateAssociative(
            <<<SQL
            SELECT BIN_TO_UUID(product_uuid) AS product_uuid, channel.code as channel, locale.code as locale, completeness.required_count, completeness.missing_count
            FROM pim_catalog_completeness completeness
            INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
            INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
            ORDER BY channel.code, locale.code;
            SQL
        );

        foreach ($rows as $row) {
            $completenesses[$row['product_uuid']][$row['channel']][$row['locale']] = [
                'missing' => (int) $row['missing_count'],
                'required' => (int) $row['required_count'],
            ];
        }

        return \array_map(
            static fn (array $completeness): string => \json_encode($completeness),
            $completenesses
        );
    }

    private function getNewCompletenesses(): array
    {
        return $this->getConnection()->fetchAllKeyValue(
            <<<SQL
            SELECT BIN_TO_UUID(product_uuid) AS product_uuid, completeness 
            FROM pim_catalog_product_completeness;
            SQL
        );
    }

    private function tableExists(string $tableName): bool
    {
        return \intval($this->getConnection()->executeQuery(
                'SHOW TABLES LIKE :tableName',
                ['tableName' => $tableName]
            )->rowCount()) >= 1;
    }
}

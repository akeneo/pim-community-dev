<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\Keywords\SQLAnywhere11Keywords;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220912132436_update_namespace_in_resource_name_for_category_history_integration extends TestCase
{
    private const MIGRATION_NAME = '_7_0_20220912132436_update_namespace_in_resource_name_for_category_history';
    use ExecuteMigrationTrait;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_resource_name_correctly(): void
    {
        $initialCorrectNamespaceCount = $this->correctNamespaceCount();
        $this->insertIncorrectNamespaceEntry();
        $this->reExecuteMigration(self::MIGRATION_NAME);

        Assert::assertEquals($this->correctNamespaceCount(), $initialCorrectNamespaceCount + 1);
        Assert::assertEquals(0, $this->wrongNamespaceCount());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertIncorrectNamespaceEntry(): void
    {
        $this->connection->executeStatement(
            <<<SQL
INSERT INTO pim_versioning_version (author, resource_name, resource_id, snapshot, changeset, version, logged_at, pending) 
VALUES ('system', 'Akeneo\\\Pim\\\Enrichment\\\Component\\\Category\\\Model\\\Category', 1, '', '', 1, NOW(), 0)
SQL
        );
    }

    private function correctNamespaceCount(): int
    {
        return
            (int) $this->connection->executeQuery(
            <<<SQL
SELECT COUNT(*) FROM pim_versioning_version
WHERE resource_name='Akeneo\\\Category\\\Infrastructure\\\Component\\\Model\\\Category'
SQL
        )->fetchOne();
    }

    private function wrongNamespaceCount(): int
    {
        return
            (int) $this->connection->executeQuery(
                <<<SQL
SELECT COUNT(*) FROM pim_versioning_version
WHERE resource_name='Akeneo\\\Pim\\\Enrichment\\\Component\\\Category\\\Model\\\Category'
SQL
            )->fetchOne();
    }
}

<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230308145000_set_empty_categories_labels_to_null_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230308145000_set_empty_categories_labels_to_null';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_set_empty_labels_to_null(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            INSERT INTO akeneo_pim_test.pim_catalog_category_translation
            (foreign_key, label, locale)
            VALUES(
                (
                    SELECT id 
                    FROM pim_catalog_category 
                    WHERE code = 'master'
                ), '', 'fr_FR');
            SQL
        );
        $this->assertEquals(1, $this->test_it_hasnt_category_label_empty());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        $this->assertEquals(0, $this->test_it_hasnt_category_label_empty());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function test_it_hasnt_category_label_empty(): int
    {
        return (int) $this->connection->fetchOne(
            <<<SQL
                SELECT count(*)
                FROM pim_catalog_category_translation
                WHERE label = ''
            SQL
        );
    }
}

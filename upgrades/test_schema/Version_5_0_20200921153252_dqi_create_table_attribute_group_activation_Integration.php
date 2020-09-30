<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

final class Version_5_0_20200921153252_dqi_create_table_attribute_group_activation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200921153252_dqi_create_table_attribute_group_activation';

    public function test_it_creates_the_attribute_group_activation_table(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS pim_data_quality_insights_attribute_group_activation;

INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) VALUES 
    ('marketing', 1, '2020-09-23 15:12:34', '2020-09-23 16:54:12'), 
    ('technical', 2, '2020-09-23 15:16:28', '2020-09-23 16:54:12')
SQL
        );

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaManager = $this->get('database_connection')->getSchemaManager();
        $this->assertTrue($schemaManager->tablesExist('pim_data_quality_insights_attribute_group_activation'));

        $this->assertAttributeGroupActivationExists('marketing', '2020-09-23 15:12:34');
        $this->assertAttributeGroupActivationExists('technical', '2020-09-23 15:16:28');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertAttributeGroupActivationExists(string $attributeGroupCode, string $updatedAt): void
    {
        $query = <<<SQL
SELECT 1 FROM pim_data_quality_insights_attribute_group_activation
WHERE attribute_group_code = :attributeGroupCode
  AND updated_at = :updatedAt
  AND activated = 1 ;
SQL;

        $attributeGroupExists = (bool) $this->get('database_connection')->executeQuery(
            $query,
            [
                'attributeGroupCode' => $attributeGroupCode,
                'updatedAt' => $updatedAt,
            ]
        )->fetchColumn();

        $this->assertTrue($attributeGroupExists);
    }
}

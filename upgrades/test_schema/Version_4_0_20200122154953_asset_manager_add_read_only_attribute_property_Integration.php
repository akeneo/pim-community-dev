<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\TestCase;

class Version_4_0_20200122154953_asset_manager_add_read_only_attribute_property_Integration extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_adds_the_is_read_only_column_to_the_attribute_table(): void
    {
        $this->dropIsReadOnlyColumn();
        $this->createAssetFamilyAndAttribute();

        $this->runMigration();

        $this->assertIsReadOnlyColumnExists();
        $this->assertIsReadOnlyValuesAreFalse();
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    private function dropIsReadOnlyColumn(): void
    {
        $this->get('database_connection')->executeQuery(
            'ALTER TABLE akeneo_asset_manager_attribute DROP COLUMN is_read_only;'
        );
    }

    private function createAssetFamilyAndAttribute(): void
    {
        $sql = <<<SQL
INSERT INTO akeneo_asset_manager_asset_family (identifier, labels, image, rule_templates, transformations, naming_convention)
VALUES ('atmosphere', '{"en_US": "Atmosphere"}', null, '[]', '[]', '[]');
SQL;
        $this->get('database_connection')->executeQuery($sql);


        $sql = <<<SQL
INSERT INTO akeneo_asset_manager_attribute (identifier, code, asset_family_identifier, labels, attribute_type, attribute_order, is_required, value_per_channel, value_per_locale, additional_properties)
VALUES ('label_atmosphere_cb856bf9-6343-4e3a-b9ed-59ef03b89185', 'label', 'atmosphere', '[]', 'text', 0, 0, 0, 1, '{"max_length": null, "is_textarea": false, "validation_rule": "none", "regular_expression": null, "is_rich_text_editor": false}');
SQL;
        $this->get('database_connection')->executeQuery($sql);
    }

    private function assertIsReadOnlyColumnExists(): void
    {
        $isFound = $this->get('database_connection')
            ->executeQuery('SHOW COLUMNS FROM akeneo_asset_manager_attribute LIKE "is_read_only";')
            ->rowCount();

        self::assertEquals(1, $isFound, 'the "is_read_only" column of the akeneo_asset_manager_attribute table was not found.');
    }

    private function assertIsReadOnlyValuesAreFalse(): void
    {
        $count = $this->get('database_connection')
            ->executeQuery('SELECT identifier FROM akeneo_asset_manager_attribute WHERE is_read_only')
            ->rowCount();

        self::assertEquals(0, $count, 'Attribute must not be in read only after the migration.');

        $count = $this->get('database_connection')
            ->executeQuery('SELECT identifier FROM akeneo_asset_manager_attribute WHERE NOT is_read_only')
            ->rowCount();

        self::assertGreaterThan(0, $count, 'No attribute found in editable mode.');
    }
}

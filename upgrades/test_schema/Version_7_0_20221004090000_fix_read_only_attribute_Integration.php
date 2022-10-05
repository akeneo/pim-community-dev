<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20221004090000_fix_read_only_attribute_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221004090000_fix_read_only_attribute';

    private Connection $connection;
    private FilePersistedFeatureFlags $featureFlags;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->featureFlags = $this->get('feature_flags');
    }

    /** @test */
    public function it_disables_readonly_attribute_when_the_feature_is_disabled(): void
    {
        $this->featureFlags->disable('read_only_product_attribute');
        $this->deleteAttribute();
        $this->insertReadOnlyAttribute();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $attribute = $this->getAttribute();

        $this->assertEquals('a:4:{s:19:"auto_option_sorting";N;s:12:"is_read_only";b:0;s:19:"reference_data_name";N;s:13:"default_value";N;}', $attribute[0]['properties']);
    }

    /** @test */
    public function it_does_not_modify_readonly_attribute_fields_when_the_feature_is_active(): void
    {
        $this->featureFlags->enable('read_only_product_attribute');
        $this->deleteAttribute();
        $this->insertReadOnlyAttribute();
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $attribute = $this->getAttribute();

        $this->assertEquals('a:4:{s:19:"auto_option_sorting";N;s:12:"is_read_only";b:1;s:19:"reference_data_name";N;s:13:"default_value";N;}', $attribute[0]['properties']);
    }

    private function deleteAttribute(): void
    {
        $sql = <<<SQL
            DELETE FROM pim_catalog_attribute WHERE `code` = :code;
        SQL;

        $this->connection->executeQuery($sql, [
            'code' => 'testing_attribute',
        ]);
    }

    private function insertReadOnlyAttribute(): void
    {
        $sql = <<<SQL
            INSERT INTO pim_catalog_attribute (group_id, sort_order, useable_as_grid_filter, max_characters,
                                          validation_rule, validation_regexp, wysiwyg_enabled, number_min,
                                          number_max, decimals_allowed, negative_allowed, date_min, date_max,
                                          metric_family, default_metric_unit, max_file_size, allowed_extensions,
                                          minimumInputLength, is_required, is_unique, is_localizable, is_scopable,
                                          code, entity_type, attribute_type, backend_type, properties, created,
                                          updated, guidelines)
            VALUES (2, 2, 1, null, null, null, null, null, null, null, null, null, null, null, null, null, '', null, 0, 0, 0, 0,
            'testing_attribute', 'Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product', 'pim_catalog_simpleselect', 'option',
            'a:4:{s:19:"auto_option_sorting";N;s:12:"is_read_only";b:1;s:19:"reference_data_name";N;s:13:"default_value";N;}',
            '2022-10-05 07:07:41', '2022-10-05 07:07:42', '[]');
        SQL;

        $this->connection->executeQuery($sql);
    }

    private function getAttribute()
    {
        $sql = <<<SQL
            SELECT properties FROM pim_catalog_attribute WHERE `code` = :code;
        SQL;

        return $this->connection->executeQuery($sql, [
            'code' => 'testing_attribute',
        ])->fetchAllAssociative();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

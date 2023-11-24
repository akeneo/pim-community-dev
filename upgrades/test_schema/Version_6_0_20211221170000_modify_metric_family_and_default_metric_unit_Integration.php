<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class Version_6_0_20211221170000_modify_metric_family_and_default_metric_unit_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211221170000_modify_metric_family_and_default_metric_unit';

    public function test_it_modify_columns_and_keep_the_data(): void
    {
        $this->resetModify();

        $metricFamily = 'family';
        $defaultMetricUnit = 'unit';
        $attributeId = $this->addAttribute($metricFamily, $defaultMetricUnit);
        $this->assertTrue($this->attributeHasMetricFamilyAndDefaultMetricUnitSet($attributeId, $metricFamily, $defaultMetricUnit));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->attributeHasMetricFamilyAndDefaultMetricUnitSet($attributeId, $metricFamily, $defaultMetricUnit));

        $longMetricFamily = str_repeat('a', 100);
        $longDefaultMetricUnit = str_repeat('a', 100);
        $attributeIdWithLongValues = $this->addAttribute($longMetricFamily, $longDefaultMetricUnit);
        $this->assertTrue($this->attributeHasMetricFamilyAndDefaultMetricUnitSet($attributeIdWithLongValues, $longMetricFamily, $longDefaultMetricUnit));
    }

    public function test_migration_is_idempotent(): void
    {
        $this->resetModify();

        $metricFamily = 'family';
        $defaultMetricUnit = 'unit';
        $attributeId = $this->addAttribute($metricFamily, $defaultMetricUnit);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        Assert::assertTrue($this->attributeHasMetricFamilyAndDefaultMetricUnitSet($attributeId, $metricFamily, $defaultMetricUnit));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function addAttribute(string $metricFamily, string $defaultMetricUnit): int
    {
        $sql = <<<SQL
            INSERT INTO pim_catalog_attribute (sort_order, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated, metric_family, default_metric_unit)
            VALUES (0, 1, 1, 1, 1, :code, 'entity', 'type', 'type', NOW(), NOW(), :metric_family, :default_metric_unit)
        SQL;

        $this->getConnection()->executeQuery($sql, [
            'code' => sprintf('code_%s', uniqid()),
            'metric_family' => $metricFamily,
            'default_metric_unit' => $defaultMetricUnit,
        ]);

        return (int) $this->getConnection()->lastInsertId();
    }

    private function attributeHasMetricFamilyAndDefaultMetricUnitSet(int $attributeId, string $metricFamily, string $defaultMetricUnit): bool
    {
        $sql = <<<SQL
            SELECT *
            FROM pim_catalog_attribute
            WHERE id = :attribute_id
        SQL;

        $result = $this->getConnection()->executeQuery($sql, ['attribute_id' => $attributeId])->fetchAssociative();

        return $result['metric_family'] === $metricFamily && $result['default_metric_unit'] === $defaultMetricUnit;
    }

    private function resetModify(): void
    {
        $this->getConnection()->executeQuery('ALTER TABLE pim_catalog_attribute MODIFY COLUMN metric_family VARCHAR(30), MODIFY COLUMN default_metric_unit VARCHAR(30)');
    }
}

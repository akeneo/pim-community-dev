<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyIntegration extends TestCase
{
    /** @var IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily */
    private $isThereAnAttributeConfiguredWithMeasurementFamily;

    public function setUp(): void
    {
        parent::setUp();
        $this->isThereAnAttributeConfiguredWithMeasurementFamily = $this->get('akeneo.pim.structure.query.is_there_at_least_one_attribute_configured_with_measurement_family');
    }

    function testItReturnsFalseIfThereIsNoAttributeConfiguredWithTheMasurementFamily()
    {
        $expected = $this->isThereAnAttributeConfiguredWithMeasurementFamily->execute('weight');

        self::assertFalse($expected);
    }

    function testItReturnsTrueIfThereIsAnAttributeConfiguredWithTheMasurementFamily()
    {
        $measurementFamily = 'weight';
        $this->createAttributeConfiguredWithMeasurementFamily($measurementFamily);

        $expected = $this->isThereAnAttributeConfiguredWithMeasurementFamily->execute($measurementFamily);

        self::assertTrue($expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributeConfiguredWithMeasurementFamily(string $measurementFamily): void
    {
        $insertAttributeGroup = <<<SQL
INSERT INTO `pim_catalog_attribute_group` (`id`, `code`, `sort_order`, `created`, `updated`)
VALUES
	(3, 'technical', 2, '2020-03-06 10:41:03', '2020-03-06 10:41:03');
SQL;

        $insertMetricAttribute = <<<SQL
INSERT INTO `pim_catalog_attribute` (`id`, `group_id`, `sort_order`, `useable_as_grid_filter`, `max_characters`, `validation_rule`, `validation_regexp`, `wysiwyg_enabled`, `number_min`, `number_max`, `decimals_allowed`, `negative_allowed`, `date_min`, `date_max`, `metric_family`, `default_metric_unit`, `max_file_size`, `allowed_extensions`, `minimumInputLength`, `is_required`, `is_unique`, `is_localizable`, `is_scopable`, `code`, `entity_type`, `attribute_type`, `backend_type`, `properties`, `created`, `updated`)
VALUES
	(7, 3, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 'Weight', 'KILOGRAM', NULL, '', NULL, 0, 0, 0, 0, :measurementFamily, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 'pim_catalog_metric', 'metric', 'a:2:{s:19:\"auto_option_sorting\";N;s:19:\"reference_data_name\";N;}', '2020-03-06 10:41:04', '2020-03-06 10:41:04');
SQL;
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $connection->executeUpdate($insertAttributeGroup);
        $connection->executeUpdate($insertMetricAttribute, ['measurementFamily' => $measurementFamily]);
    }
}

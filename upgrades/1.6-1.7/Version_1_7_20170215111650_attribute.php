<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Set to null (instead of "") "metricFamily" & "defaultMetricUnit" fields in "attribute" table
 */
class Version_1_7_20170215111650_attribute extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $tableHelper = new SchemaHelper($this->container);
        $attributeTable = $tableHelper->getTableOrCollection('attribute');

        $stmt = $this->connection->prepare(sprintf(
            'SELECT id, metric_family, default_metric_unit FROM %s',
            $attributeTable
        ));

        $stmt->execute();
        $attributes = $stmt->fetchAll();

        foreach ($attributes as $attribute) {
            if ('' == $attribute['metric_family']) {
                $attribute['metric_family'] = null;
            }

            if ('' == $attribute['default_metric_unit']) {
                $attribute['default_metric_unit'] = null;
            }

            $this->connection->update(
                $attributeTable,
                [
                    'metric_family'       => $attribute['metric_family'],
                    'default_metric_unit' => $attribute['default_metric_unit']
                ],
                ['id'                  => $attribute['id']]
            );
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}

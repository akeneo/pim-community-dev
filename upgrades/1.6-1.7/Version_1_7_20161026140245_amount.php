<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Change "akeneo_batch_job_instance" to change keys "data" to "amount" for metric & prices attributes
 */
class Version_1_7_20161026140245_amount extends AbstractMigration
{
    const JOB_INSTANCE_TABLE = 'akeneo_batch_job_instance';
    const ATTRIBUTE_TABLE = 'pim_catalog_attribute';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $attributeTypes = $this->getAttributeByTypes();
        $stmt = $this->connection->prepare(sprintf(
            'SELECT id, raw_parameters FROM %s WHERE job_name IN ("csv_product_export", "xlsx_product_export")',
            self::JOB_INSTANCE_TABLE
        ));

        $stmt->execute();
        $jobs = $stmt->fetchAll();

        foreach ($jobs as $job) {
            $parameters = unserialize($job['raw_parameters']);
            if (isset($parameters['filters']['data'])) {
                foreach ($parameters['filters']['data'] as $index => $data) {
                    if (in_array($data['field'], $attributeTypes)) {
                        $data['value']['amount'] = $data['value']['data'];
                        unset($data['value']['data']);

                        $parameters['filters']['data'][$index] = $data;
                    }
                }

                $this->connection->update(
                    self::JOB_INSTANCE_TABLE,
                    ['raw_parameters' => serialize($parameters)],
                    ['id'             => $job['id']]
                );
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * @throws DBALException
     *
     * @return array
     */
    protected function getAttributeByTypes()
    {
        $stmt = $this->connection->prepare(sprintf('SELECT code FROM %s WHERE attribute_type IN ("%s", "%s")',
            self::ATTRIBUTE_TABLE, AttributeTypes::PRICE_COLLECTION, AttributeTypes::METRIC
        ));

        $stmt->execute();

        $attributes = [];
        foreach ($stmt->fetchAll() as $attribute) {
            $attributes[] = $attribute['code'];
        }

        return $attributes;
    }
}

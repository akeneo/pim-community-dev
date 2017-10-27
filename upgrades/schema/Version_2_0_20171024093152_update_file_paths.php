<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * This migration will udpate the configuration of the quick export job profiles to
 * - update filePath to filePathProduct
 * - create a new parameter filePathProductModel
 */
class Version_2_0_20171024093152_update_file_paths extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected const JOB_INSTANCE_CODES = [
        'csv_product_grid_context_quick_export',
        'csv_product_quick_export',
        'xlsx_product_grid_context_quick_export',
        'xlsx_product_quick_export',
    ];

    /**
     * @param Schema $schema
     *
     * @throws \Exception
     */
    public function up(Schema $schema)
    {
        $schemaHelper = new SchemaHelper($this->container);
        $jobInstanceTable = $schemaHelper->getTableOrCollection('job_instance');

        $stmt = $this->connection->executeQuery(
            sprintf('SELECT code, raw_parameters FROM %s WHERE code IN (?)', $jobInstanceTable),
            [self::JOB_INSTANCE_CODES],
            [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );

        $jobInstances = $stmt->fetchAll();

        if (null === $jobInstances) {
            throw new \Exception('No job instance has been found.');
        }

        $migratedJobInstances = [];
        foreach ($jobInstances as $jobInstance) {
            $parameters = unserialize($jobInstance['raw_parameters']);
            $parameters['filePathProduct'] = $this->generateProductPath($parameters['filePath']);
            $parameters['filePathProductModel'] = $this->generateProductModelPath($parameters['filePath']);
            unset($parameters['filePath']);
            $jobInstance['raw_parameters'] = serialize($parameters);

            $migratedJobInstances[] = $jobInstance;
        }

        $this->connection->beginTransaction();

        try {
            foreach ($migratedJobInstances as $migratedJobInstance) {
                $this->connection->update(
                    'akeneo_batch_job_instance',
                    ['raw_parameters' => $migratedJobInstance['raw_parameters']],
                    ['code'           => $migratedJobInstance['code']]
                );
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
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
     * If the previous file path contains "product", returns a the same string with "product_models" instead of it.
     * If not, returns the name of the file with _product_models at the end of the file name.
     *
     * Ex:
     * "/tmp/export_products.csv" => "/tmp/2_export_product_models.csv"
     * "/tmp/custom_export.csv"   => "/tmp/2_custom_export_product_models.csv"
     *
     * @param string $filePath
     *
     * @return string
     */
    private function generateProductModelPath($filePath)
    {
        $fileParts = pathinfo($filePath);

        if (strpos($fileParts['filename'], 'products') !== false) {
            return sprintf(
                '%s%s2_%s.%s',
                $fileParts['dirname'],
                DIRECTORY_SEPARATOR,
                preg_replace('/products/', 'product_models', $fileParts['filename'], 1),
                $fileParts['extension']
            );
        }

        return sprintf(
            '%s%s2_%s_product_models.%s',
            $fileParts['dirname'],
            DIRECTORY_SEPARATOR,
            $fileParts['filename'],
            $fileParts['extension']
        );
    }

    /**
     * Returns the current filePath, preempted with '1_' in the filename.
     *
     * Ex: "/tmp/export_products.csv" => "/tmp/1_export_products.csv"
     *
     * @param string $filePath
     *
     * @return string
     */
    private function generateProductPath($filePath)
    {
        $fileParts = pathinfo($filePath);

        return sprintf(
            '%s%s1_%s.%s',
            $fileParts['dirname'],
            DIRECTORY_SEPARATOR,
            $fileParts['filename'],
            $fileParts['extension']
        );
    }
}

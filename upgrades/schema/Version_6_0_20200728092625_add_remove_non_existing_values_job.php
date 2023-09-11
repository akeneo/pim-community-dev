<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the remove_non_existing_product_values job
 */
final class Version_6_0_20200728092625_add_remove_non_existing_values_job extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if ($this->jobExists('remove_non_existing_product_values')) {
            return;
        }

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type);
SQL;
        $this->addSql($sql, [
           'code' =>  'remove_non_existing_product_values',
           'label' =>  'Remove the non existing values of product and product models',
           'job_name' =>  'remove_non_existing_product_values',
           'status' =>  0,
           'connector' =>  'internal',
           'raw_parameters' =>  'a:0:{}',
           'type' =>  'remove_non_existing_product_values',
        ]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobExists(string $jobCode): bool
    {
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $jobCode]
        );

        return 1 <= $stmt->rowCount();
    }
}

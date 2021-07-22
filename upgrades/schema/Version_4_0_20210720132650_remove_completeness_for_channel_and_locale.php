<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the remove_completeness_for_channel_and_locale
 */
final class Version_4_0_20210720132650_remove_completeness_for_channel_and_locale extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if ($this->jobExists('remove_completeness_for_channel_and_locale')) {
            return;
        }

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type);
SQL;
        $this->addSql($sql, [
            'code' =>  'remove_completeness_for_channel_and_locale',
            'label' =>  'Remove completeness for channel and locale',
            'job_name' =>  'remove_completeness_for_channel_and_locale',
            'status' =>  0,
            'connector' =>  'internal',
            'raw_parameters' =>  'a:0:{}',
            'type' =>  'remove_completeness_for_channel_and_locale',
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

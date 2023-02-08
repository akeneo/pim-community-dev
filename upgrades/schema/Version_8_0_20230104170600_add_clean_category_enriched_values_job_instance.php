<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230104170600_add_clean_category_enriched_values_job_instance extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the clean_categories_enriched_values job instance';
    }

    public function up(Schema $schema): void
    {
        if (!$this->jobInstanceExists('clean_categories_enriched_values')) {
            $this->addSql(
                <<<SQL
                INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
                VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type)
                ON DUPLICATE KEY UPDATE code = code;
                SQL,
                [
                    'code' => 'clean_categories_enriched_values',
                    'label' => 'Clean categories enriched values',
                    'job_name' => 'clean_categories_enriched_values',
                    'status' => 0,
                    'connector' => 'internal',
                    'raw_parameters' => \serialize([]),
                    'type' => 'clean_categories_enriched_values',
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobInstanceExists(string $jobCode): bool
    {
        $sql = <<<SQL
            SELECT id
            FROM akeneo_batch_job_instance
            WHERE code = :jobCode
        SQL;

        return 0 !== $this->connection->executeQuery($sql, ['jobCode' => $jobCode])->rowCount();
    }
}

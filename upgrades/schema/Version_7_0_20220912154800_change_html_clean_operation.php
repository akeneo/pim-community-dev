<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\MigrationException;

final class Version_7_0_20220912154800_change_html_clean_operation extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $jobs = $this->fetchTailoredImportJob();
        $jobToFix = [];

        foreach ($jobs as $job) {
            $rawParameters = unserialize($job['raw_parameters']);
            foreach ($rawParameters['import_structure']['data_mappings'] as &$dataMapping) {
                foreach ($dataMapping['operations'] as &$operation) {
                    if ($operation['type'] === 'clean_html_tags') {
                        $operation['type'] = "clean_html";
                        $operation['modes'] = ['remove', 'decode'];
                        $jobToFix[] = ['id' => $job['id'], 'raw_parameters' => serialize($rawParameters)];
                    }
                }
            }
        }

        if (!empty($jobToFix)) {
            $this->updateTailoredImportJob($jobToFix);
        }

    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function fetchTailoredImportJob(): array
    {
        $sql = <<<SQL
            SELECT id, raw_parameters FROM akeneo_batch_job_instance WHERE connector = 'Akeneo Tailored Import';
        SQL;

        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

    public function updateTailoredImportJob(Array $jobs): void
    {
        $sql = <<<SQL
            UPDATE akeneo_batch_job_instance SET raw_parameters = :raw_parameters WHERE id = :id;
        SQL;

        foreach ($jobs as $job) {
            $this->addSql($sql, $job);
        }
    }
}

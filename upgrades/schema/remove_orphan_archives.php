<?php

// This script is used to remove import/export archives that are not related to a job execution anymore.
//
// In the archive storage, the directory structure is as below:
//      |- JOB_TYPE/
//            |- JOB_NAME/
//                  |- JOB_EXECUTION_ID/
//
// For instance:
//      |- export/
//            |- csv_attribute_export/
//                  |- 21/
//                  |- 45/
//            |- csv_product_export/
//                  |- 22/
//            |- xlsx_product_model_export/
//                  |- 23/
//      |- import/
//            |- csv_attribute_import/
//                  |- 799/
//                  |- 1089/
//
// Please note that this script should be executed BEFORE upgrades/schema/archived_logs_local_filesystem_to_object_storage.php
// and upgrades/schema/archived_files_local_filesystem_to_object_storage.php.

use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

if (!file_exists(__DIR__ . '/../../app/AppKernel.php')) {
    die("Please run this command from your Symfony application root.");
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/AppKernel.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->load($envFile);
}

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$archiveDir = $container->getParameter('archive_dir');
$database = $container->get('doctrine.dbal.default_connection');

echo "Removing orphan directories in the local archive storage $archiveDir...\n";
removeOrphanJobExecutionDirectories($database, $archiveDir);
removeOrphanJobDirectories($database, $archiveDir);
echo "Done!\n";

function removeOrphanJobExecutionDirectories(Connection $database, string $archiveDir): void
{
    /**
     * Existing job executions for imports/exports. For instance:
     *
     * +-------------------+---------------------------+--------+
     * | job_execution_ids | code                      | type   |
     * +-------------------+---------------------------+--------+
     * | 21,19             | csv_attribute_export      | export |
     * | 22                | csv_product_export        | export |
     * | 25                | xlsx_product_model_export | export |
     * | 32,30             | csv_attribute_import      | import |
     * +-------------------+---------------------------+--------+
     */
    $existingJobExecutions = <<<SQL
SELECT GROUP_CONCAT(je.id) AS job_execution_ids, ji.code, ji.type 
FROM akeneo_batch_job_execution je 
INNER JOIN akeneo_batch_job_instance ji 
    ON ji.id = je.job_instance_id 
WHERE ji.type IN ('import', 'export') 
GROUP BY ji.type, ji.code;
SQL;

    $stmt = $database->executeQuery($existingJobExecutions);
    while (false !== ($job = $stmt->fetch())) {
        removeOrphanJobExecutionDirectoriesForJob($archiveDir, $job);
    }
}

function removeOrphanJobDirectories(Connection $database, string $archiveDir): void
{
    $existingJobsWithJobExecutions = <<<SQL
SELECT ji.code
FROM akeneo_batch_job_execution je 
INNER JOIN akeneo_batch_job_instance ji 
    ON ji.id = je.job_instance_id 
WHERE ji.type IN ('import', 'export');
SQL;

    $stmt = $database->executeQuery($existingJobsWithJobExecutions);
    $existingJobNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    $localDirectories = (new Finder())->directories()->depth(0)->in(
        [$archiveDir . DIRECTORY_SEPARATOR . 'export', $archiveDir . DIRECTORY_SEPARATOR . 'import']
    );

    foreach ($localDirectories as $localDirectory) {
        $jobName = basename($localDirectory);

        if (!in_array($jobName, $existingJobNames)) {
            removeDirectory($localDirectory);
        }
    }
}

function removeOrphanJobExecutionDirectoriesForJob(string $archiveDir, array $job)
{
    $localDirectories = getLocalDirectoriesForJob($archiveDir, $job['type'], $job['code']);
    foreach ($localDirectories as $directory) {
        if (doesDirectoryBelongToDeletedJobExecution($directory, $job['job_execution_ids'])) {
            removeDirectory($directory);
        }
    }
}

function doesDirectoryBelongToDeletedJobExecution(string $directory, string $existingJobExecutionIds): bool
{
    $ids = explode(',', $existingJobExecutionIds);
    $directoryId = basename($directory);

    return !in_array($directoryId, $ids);
}

function getLocalDirectoriesForJob(string $archiveDir, string $jobType, string $jobName): Finder
{
    $localStorageDirectory = $archiveDir . DIRECTORY_SEPARATOR . $jobType . DIRECTORY_SEPARATOR . $jobName;

    $finder = new Finder();
    $finder->directories()->depth(0)->in($localStorageDirectory);

    return $finder;
}

function removeDirectory(string $directory): void
{
    (new Filesystem())->remove($directory);
    echo "Directory $directory removed.\n";
}

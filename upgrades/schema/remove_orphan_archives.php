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

echo "Purging orphan directories from local archives storage $archiveDir...\n\n";
echo "Purging orphan job execution directories...\n";
removeOrphanJobExecutionDirectories($database, $archiveDir);
echo "\nPurging orphan job directories...\n";
removeOrphanJobDirectories($database, $archiveDir);
echo "\nDone!\n";

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
SELECT GROUP_CONCAT(je.id) AS job_execution_ids, ji.job_name, ji.type 
FROM akeneo_batch_job_execution je 
INNER JOIN akeneo_batch_job_instance ji 
    ON ji.id = je.job_instance_id 
WHERE ji.type IN ('import', 'export') 
GROUP BY ji.type, ji.job_name;
SQL;

    $stmt = $database->executeQuery($existingJobExecutions);
    while (false !== ($job = $stmt->fetch())) {
        removeOrphanJobExecutionDirectoriesForJob($archiveDir, $job);
    }
}

function removeOrphanJobDirectories(Connection $database, string $archiveDir): void
{
    $existingJobsWithJobExecutions = <<<SQL
SELECT DISTINCT ji.job_name
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
        echo "  - Processing job directory $localDirectory.\n";
        $jobName = basename($localDirectory);

        if (!in_array($jobName, $existingJobNames)) {
            removeDirectory($localDirectory);
        }
    }
}

function removeOrphanJobExecutionDirectoriesForJob(string $archiveDir, array $job)
{
    $jobDirectory = $archiveDir . DIRECTORY_SEPARATOR . $job['type'] . DIRECTORY_SEPARATOR . $job['job_name'];
    echo "  - Looking for job execution directories to remove in $jobDirectory.\n";

    if (!(new Filesystem())->exists($jobDirectory)) {
        return;
    }

    $jobExecutionDirectories = getLocalDirectoriesForJob($jobDirectory);
    foreach ($jobExecutionDirectories as $directory) {
        echo "  - Job execution directory $directory found.\n";
        if (isDirectoryOrphan($directory, $job['job_execution_ids'])) {
            removeDirectory($directory);
        }
    }
}

function isDirectoryOrphan(string $directory, string $existingJobExecutionIds): bool
{
    $ids = explode(',', $existingJobExecutionIds);
    $directoryId = basename($directory);

    return !in_array($directoryId, $ids);
}

function getLocalDirectoriesForJob(string $localStorageDirectory): Finder
{
    $finder = new Finder();
    $finder->directories()->depth(0)->in($localStorageDirectory);

    return $finder;
}

function removeDirectory(string $directory): void
{
    if ((new Filesystem())->exists($directory)) {
        // using directly rm -rf instead of Filesytem::remove for NFS performance/stability problems
        exec("rm -rf $directory");
        echo "  - Directory $directory removed.\n";
    }
}

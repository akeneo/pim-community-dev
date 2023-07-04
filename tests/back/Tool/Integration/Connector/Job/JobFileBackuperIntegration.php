<?php

namespace AkeneoTest\Tool\Integration\Connector\Job;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\FilesystemOperator;

class JobFileBackuperIntegration extends TestCase
{
    private FilesystemOperator $archivistFilesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->archivistFilesystem = $this->get('oneup_flysystem.archivist_filesystem');
        foreach ($this->archivistFilesystem->listContents('.') as $content) {
            $this->archivistFilesystem->deleteDirectory($content->path());
        }
    }

    public function test_it_backups_a_file(): void
    {
        $localFilePath = __DIR__ . '/dummy_export.csv';
        $jobExecution = $this->createJobExecution();
        $backupFilePath = $this->getBackupFilePath($jobExecution, $localFilePath);

        $backuper = $this->get('pim_connector.job.file_backuper');
        $backuper->backup($jobExecution, $localFilePath);

        $fileExists = $this->archivistFilesystem->fileExists($backupFilePath);
        $this->assertTrue($fileExists);
    }

    public function test_it_recovers_a_file(): void
    {
        $localFilePath = '/tmp/dummy_export.csv';

        $jobExecution = $this->createJobExecution();
        $backupFilePath = $this->getBackupFilePath($jobExecution, $localFilePath);
        $this->archivistFilesystem->writeStream($backupFilePath, fopen(__DIR__ . '/dummy_export.csv', 'r'));

        $this->assertFileDoesNotExist($localFilePath);

        $backuper = $this->get('pim_connector.job.file_backuper');
        $backuper->recover($jobExecution, $localFilePath);

        $this->assertFileExists($localFilePath);
    }

    private function createJobExecution(): JobExecution
    {
        $job = $this->get('akeneo_batch.job.job_registry')->get('csv_product_export');
        $jobInstance = $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('csv_product_export');

        return $this->get('akeneo_batch.job_repository')->createJobExecution($job, $jobInstance, new JobParameters([]));
    }

    private function getBackupFilePath(JobExecution $jobExecution, string $filePath): string
    {
        $jobInstance = $jobExecution->getJobInstance();
        return sprintf(
            '%s/%s/%d/%s/%s',
            $jobInstance->getType(),
            $jobInstance->getCode(),
            $jobExecution->getId(),
            'backup',
            basename($filePath),
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

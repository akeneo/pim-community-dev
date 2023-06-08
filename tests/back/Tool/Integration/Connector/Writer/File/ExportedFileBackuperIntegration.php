<?php

namespace AkeneoTest\Tool\Integration\Connector\Writer\File;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class ExportedFileBackuperIntegration extends TestCase
{
    public function test_it_backups_a_file(): void
    {
        $filePath = __DIR__ . '/dummy_export.csv';
        $jobExecution = $this->createJobExecution();
        $jobInstance = $jobExecution->getJobInstance();

        $expectedBackupFilePath = sprintf(
            '%s/%s/%d/%s/%s',
            $jobInstance->getType(),
            $jobInstance->getCode(),
            $jobExecution->getId(),
            'backup',
            basename($filePath)
        );

        $backuper = $this->get('pim_connector.writer.file.backuper');
        $actualBackupFilePath = $backuper->backup($jobExecution, $filePath);

        $this->assertSame($expectedBackupFilePath, $actualBackupFilePath);

        $fileExists = $this->get('oneup_flysystem.archivist_filesystem')->fileExists($actualBackupFilePath);
        $this->assertTrue($fileExists);
    }

    private function createJobExecution(): JobExecution
    {
        $job = $this->get('akeneo_batch.job.job_registry')->get('csv_product_export');
        $jobInstance = $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('csv_product_export');

        return $this->get('akeneo_batch.job_repository')->createJobExecution($job, $jobInstance, new JobParameters([]));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
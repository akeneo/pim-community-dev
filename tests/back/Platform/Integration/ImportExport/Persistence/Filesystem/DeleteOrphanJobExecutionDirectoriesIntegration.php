<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Persistence\Filesystem;

use Akeneo\Platform\Bundle\ImportExportBundle\Persistence\Filesystem\DeleteOrphanJobExecutionDirectories;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Assert;

class DeleteOrphanJobExecutionDirectoriesIntegration extends TestCase
{
    /** @var FilesystemOperator */
    private $archivistFilesystem;

    /** @var JobExecutionRepository */
    private $jobExecutionRepository;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var DeleteOrphanJobExecutionDirectories */
    private $deleteOrphansJobExecutionFiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->archivistFilesystem = $this->get('oneup_flysystem.archivist_filesystem');
        $this->jobExecutionRepository = $this->get('akeneo_batch.job_repository');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
        $this->deleteOrphansJobExecutionFiles = $this->get('akeneo.platform.import_export.filesystem.delete_orphans_job_execution_directories');
    }

    /**
     * @test
     */
    public function it_deletes_orphan_job_execution_files()
    {
        $jobInstanceCode = 'edit_common_attributes';
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($jobInstanceCode);
        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $jobExecution = $this->jobExecutionRepository->createJobExecution($job, $jobInstance, new JobParameters([]));

        $jobExecutionFilePath = $this->filepathFromJobExecution($jobExecution);
        $orphanFile1 = \sprintf('type/job_name_1/%d/logs/logs.log', $jobExecution->getId() + 1);
        $orphanFile2 = \sprintf('type/job_name_1/%d/output/output.csv', $jobExecution->getId() + 1);
        $orphanFile3 = \sprintf('type/job_name_1/%d/output/output2.csv', $jobExecution->getId() + 1);
        $orphanFile4 = \sprintf('type/job_name_2/%d/logs/logs.log', $jobExecution->getId() + 1);

        $this->archivistFilesystem->write($jobExecutionFilePath, 'content');
        $this->archivistFilesystem->write($orphanFile1, 'content');
        $this->archivistFilesystem->write($orphanFile2, 'content');
        $this->archivistFilesystem->write($orphanFile3, 'content');
        $this->archivistFilesystem->write($orphanFile4, 'content');

        Assert::assertTrue($this->archivistFilesystem->fileExists($jobExecutionFilePath));
        Assert::assertTrue($this->archivistFilesystem->fileExists($orphanFile1));
        Assert::assertTrue($this->archivistFilesystem->fileExists($orphanFile2));
        Assert::assertTrue($this->archivistFilesystem->fileExists($orphanFile3));
        Assert::assertTrue($this->archivistFilesystem->fileExists($orphanFile4));

        $this->deleteOrphansJobExecutionFiles->execute();

        Assert::assertTrue($this->archivistFilesystem->fileExists($jobExecutionFilePath));
        Assert::assertFalse($this->archivistFilesystem->fileExists($orphanFile1));
        Assert::assertFalse($this->archivistFilesystem->fileExists(\dirname($orphanFile1)));
        Assert::assertFalse($this->archivistFilesystem->fileExists($orphanFile2));
        Assert::assertFalse($this->archivistFilesystem->fileExists(\dirname($orphanFile2)));
        Assert::assertFalse($this->archivistFilesystem->fileExists($orphanFile3));
        Assert::assertFalse($this->archivistFilesystem->fileExists(\dirname($orphanFile3)));
        Assert::assertFalse($this->archivistFilesystem->fileExists($orphanFile4));
        Assert::assertFalse($this->archivistFilesystem->fileExists(\dirname($orphanFile4)));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function filepathFromJobExecution(JobExecution $jobExecution): string
    {
        $jobInstance = $jobExecution->getJobInstance();
        $archiveName = 'logs';

        return
            $jobInstance->getType() . DIRECTORY_SEPARATOR .
            $jobInstance->getJobName() . DIRECTORY_SEPARATOR .
            $jobExecution->getId() . DIRECTORY_SEPARATOR .
            $archiveName . DIRECTORY_SEPARATOR .
            'logs.log';
    }
}

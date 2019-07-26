<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\Repository\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Persistence\Filesystem\DeleteOrphanJobExecutionDirectories;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Assert;

class DeleteOrphanJobExecutionDirectoriesIntegration extends TestCase
{
    /** @var Filesystem */
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
    public function it_delete_orphans_job_execution_files()
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('edit_common_attributes');
        $jobExecution = $this->jobExecutionRepository->createJobExecution($jobInstance, new JobParameters([]));

        $jobExecutionFilePath = $this->filepathFromJobExecution($jobExecution);
        $orphanFile1 = 'type/job_name_1/1/logs/logs.log';
        $orphanFile2 = 'type/job_name_1/1/output/output.log';
        $orphanFile3 = 'type/job_name_1/2/logs/logs.log';
        $orphanFile4 = 'type/job_name_2/1/logs/logs.log';

        $this->archivistFilesystem->write($jobExecutionFilePath, 'content');
        $this->archivistFilesystem->write($orphanFile1, 'content');
        $this->archivistFilesystem->write($orphanFile2, 'content');
        $this->archivistFilesystem->write($orphanFile3, 'content');
        $this->archivistFilesystem->write($orphanFile4, 'content');

        Assert::assertTrue($this->archivistFilesystem->has($jobExecutionFilePath));
        Assert::assertTrue($this->archivistFilesystem->has($orphanFile1));
        Assert::assertTrue($this->archivistFilesystem->has($orphanFile2));
        Assert::assertTrue($this->archivistFilesystem->has($orphanFile3));
        Assert::assertTrue($this->archivistFilesystem->has($orphanFile4));

        $this->deleteOrphansJobExecutionFiles->execute();

        Assert::assertTrue($this->archivistFilesystem->has($jobExecutionFilePath));
        Assert::assertFalse($this->archivistFilesystem->has($orphanFile1));
        Assert::assertFalse($this->archivistFilesystem->has($orphanFile2));
        Assert::assertFalse($this->archivistFilesystem->has($orphanFile3));
        Assert::assertFalse($this->archivistFilesystem->has($orphanFile4));

        Assert::assertFalse($this->archivistFilesystem->has('type/job_name_1/1'));
        Assert::assertFalse($this->archivistFilesystem->has('type/job_name_1/2'));
        Assert::assertFalse($this->archivistFilesystem->has('type/job_name_2/1'));
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

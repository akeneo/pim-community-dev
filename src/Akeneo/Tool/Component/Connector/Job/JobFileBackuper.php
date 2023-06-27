<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Job;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\FilesystemOperator;

class JobFileBackuper
{
    private const BACKUP_DIR = 'backup';

    public function __construct(
        private readonly FilesystemOperator $filesystemOperator,
    ) {
    }

    public function backup(JobExecution $jobExecution, string $localFilePath): void
    {
        $backupPath = $this->getBackupPath($jobExecution, basename($localFilePath));
        $this->filesystemOperator->write($backupPath, file_get_contents($localFilePath));
    }

    public function recover(JobExecution $jobExecution, string $localFilePath): void
    {
        $backupPath = $this->getBackupPath($jobExecution, basename($localFilePath));
        file_put_contents($localFilePath, $this->filesystemOperator->readStream($backupPath));
    }

    public function clean(JobExecution $jobExecution): void
    {
        $backupDir = $this->getBackupDir($jobExecution);

        if ($this->filesystemOperator->directoryExists($backupDir)) {
            $this->filesystemOperator->deleteDirectory($backupDir);
        }
    }

    private function getBackupDir(JobExecution $jobExecution): string
    {
        return sprintf(
            '%s/%s/%s/%s',
            $jobExecution->getJobInstance()->getType(),
            $jobExecution->getJobInstance()->getJobName(),
            $jobExecution->getId(),
            self::BACKUP_DIR,
        );
    }

    private function getBackupPath(JobExecution $jobExecution, string $fileName): string
    {
        return sprintf(
            '%s/%s',
            $this->getBackupDir($jobExecution),
            $fileName,
        );
    }
}

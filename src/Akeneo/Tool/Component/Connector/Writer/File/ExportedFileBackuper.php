<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\FilesystemOperator;

class ExportedFileBackuper
{
    private const BACKUP_DIR = 'backup';

    public function __construct(
        private FilesystemOperator $filesystemOperator,
    )
    {}

    public function backup(JobExecution $jobExecution, string $filePath): string
    {
        $backupPath = $this->getRelativeBackupPath($jobExecution, $filePath);
        $this->filesystemOperator->write($backupPath, file_get_contents($filePath));

        return $backupPath;
    }

    private function getRelativeBackupPath(JobExecution $jobExecution, string $filePath): string
    {
        $jobInstance = $jobExecution->getJobInstance();

        return
            $jobInstance->getType() . DIRECTORY_SEPARATOR .
            $jobInstance->getJobName() . DIRECTORY_SEPARATOR .
            $jobExecution->getId() . DIRECTORY_SEPARATOR .
            self::BACKUP_DIR . DIRECTORY_SEPARATOR .
            basename($filePath);
    }
}
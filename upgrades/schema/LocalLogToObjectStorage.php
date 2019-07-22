<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use League\Flysystem\Filesystem;

/**
 * Relocates import/export logs from a local filesystem to an object storage.
 *
 * The log files are listed from the akeneo_batch_job_execution table. Then, each existing and readable local file is
 * stored in the object storage.
 */
class LocalLogToObjectStorage
{
    /** @var Filesystem */
    private $objectStorage;

    /** @var Connection */
    private $database;

    /** @var array */
    private $errors = [];

    /** @var int */
    private $countRelocated = 0;

    public function __construct(
        Filesystem $filesystem,
        Connection $database
    ) {
        $this->objectStorage = $filesystem;
        $this->database = $database;
    }

    public function countFiles(): int
    {
        return $this->database->executeQuery(
            'SELECT COUNT(*) FROM akeneo_batch_job_execution je INNER JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id WHERE ji.type IN (\'export\', \'import\')'
        )->fetchColumn();
    }

    public function countRelocated(): int
    {
        return $this->countRelocated;
    }

    public function relocateFiles(): array
    {
        $stmt = $this->database->executeQuery(
            'SELECT ji.type, ji.job_name, je.id, je.log_file FROM akeneo_batch_job_execution je INNER JOIN akeneo_batch_job_instance ji ON je.job_instance_id = ji.id WHERE ji.type IN (\'export\', \'import\')'
        );

        while (false !== ($logInfo = $stmt->fetch())) {
            try {
                $this->relocate($logInfo);
            } catch (RelocateException $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        return $this->errors;
    }

    private function relocate(array $logInfo): void
    {
        $file = $this->open($logInfo);

        $stored = $this->objectStorage->putStream($this->logKey($logInfo), $file);
        if (!$stored) {
            if (is_resource($file)) {
                fclose($file);
            }

            throw new StoreException($logInfo['file_key']);
        }

        if (is_resource($file)) {
            fclose($file);
        }

        $this->countRelocated++;
    }

    private function open(array $logInfo)
    {
        $filePath = $logInfo['log_file'];
        $file = @fopen($filePath, 'r');
        if (false === $file) {
            throw new OpenFileException($filePath);
        }

        return $file;
    }

    /**
     * Build the log key the same way that in LogKey {@see \Akeneo\Tool\Component\Connector\LogKey}
     */
    private function logKey(array $logInfo): string
    {
        return
            $logInfo['type'] . DIRECTORY_SEPARATOR .
            $logInfo['job_name'] . DIRECTORY_SEPARATOR .
            $logInfo['id'] . DIRECTORY_SEPARATOR .
            'log' . DIRECTORY_SEPARATOR .
            basename($logInfo['log_file'])
        ;
    }
}

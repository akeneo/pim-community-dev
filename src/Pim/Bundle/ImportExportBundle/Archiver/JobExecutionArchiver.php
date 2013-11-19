<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\ImportExportBundle\Reader\File\CsvReader;
use Pim\Bundle\ImportExportBundle\Writer\File\FileWriter;
use Pim\Bundle\ImportExportBundle\Writer\File\ArchivableWriterInterface;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionArchiver
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Archive files used by job execution (input / ouptput)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();
        $archivePath = $this->getJobExecutionPath($jobExecution);

        $job         = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            $reader = $step->getReader();
            $writer = $step->getWriter();
            if ($reader instanceof CsvReader) {
                $sourcePath = $reader->getFilePath();
                if (file_exists($sourcePath)) {
                    $this->copyFile($sourcePath, $archivePath);
                }
            }
            if ($writer instanceof FileWriter) {
                $sourcePath = $writer->getPath();
                if ($writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1) {
                    $archivePath = sprintf('%s/%s.zip', $archivePath, pathinfo($sourcePath, PATHINFO_FILENAME));
                    $this->createZipArchive($writer->getWrittenFiles(), $archivePath);
                } elseif (file_exists($sourcePath)) {
                    $this->copyFile($sourcePath, $archivePath);
                }
            }
        }
    }

    /**
     * Get download file path
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    public function getDownloadPath(JobExecution $jobExecution)
    {
        $path = $this->getJobExecutionPath($jobExecution);

        if (is_dir($path)) {
            $files     = scandir($path);
            $files     = array_diff($files, array('.', '..'));
            $firstFile = current($files);
            $path      = $path.$firstFile;
        }

        return $path;
    }

    /**
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    public function getJobExecutionPath(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();
        $jobType     = $jobInstance->getType();
        $path        = $jobInstance->getAlias().DIRECTORY_SEPARATOR.$jobExecution->getId().DIRECTORY_SEPARATOR;

        return $this->getBaseDirectory($jobType).$path;
    }

    /**
     * @param string $jobType
     *
     * @return string
     */
    public function getBaseDirectory($jobType)
    {
        return $this->rootDir.DIRECTORY_SEPARATOR.$jobType.DIRECTORY_SEPARATOR;
    }

    /**
     * Copy the source path to the archive
     * @param string $sourcePath
     * @param string $archivePath
     */
    protected function copyFile($sourcePath, $archivePath)
    {
        $this->ensureDir($archivePath);
        $sourceName = basename($sourcePath);
        $destPath   = $archivePath.$sourceName;
        copy($sourcePath, $destPath);
    }

    /**
     * Ensure that a directory exists - if it doesn't, it will be created
     * @param string $directory
     */
    protected function ensureDir($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Create a zip archive with the execution results.
     *
     * @param array  $writtenFiles
     * @param string $archivePath
     *
     * @throws \RuntimeException If an error occurs when creating the archive
     */
    protected function createZipArchive($writtenFiles, $archivePath)
    {
        $this->ensureDir(pathinfo($archivePath, PATHINFO_DIRNAME));

        $archive = new \ZipArchive();
        $status = $archive->open($archivePath, \ZIPARCHIVE::CREATE);
        if ($status !== true) {
            throw new \RuntimeException(sprintf('Error "%d" occured when creating the zip archive.', $status));
        }

        foreach ($writtenFiles as $fullPath => $localPath) {
            $status = $archive->addFile($fullPath, $localPath);
            if ($status !== true) {
                throw new \RuntimeException(
                    sprintf(
                        'Unknown error occured when adding file "%s" to the zip archive.',
                        $fullPath
                    )
                );
            }
        }

        $archive->close();
    }
}

<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\ImportExportBundle\Reader\CsvReader;
use Pim\Bundle\ImportExportBundle\Writer\FileWriter;

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
                $this->copyFile($sourcePath, $archivePath);
            }
            if ($writer instanceof FileWriter) {
                $sourcePath = $writer->getPath();
                $this->copyFile($sourcePath, $archivePath);
            }
        }
    }

    /**
     * Copy the source path to the archive
     * @param string $sourcePath
     * @param string $archivepath
     */
    protected function copyFile($sourcePath, $archivePath)
    {
        $sourceName = basename($sourcePath);
        $destPath   = $archivePath.$sourceName;
        mkdir($archivePath, 0777, true);
        copy($sourcePath, $destPath);
    }

    /**
     * Get download file path
     *
     * @return string
     */
    public function getDownloadPath(JobExecution $jobExecution)
    {
        $directory = $this->getJobExecutionPath($jobExecution);
        $files     = scandir($directory);
        $files     = array_diff($files, array('.', '..'));
        $firstFile = current($files);
        $path      = $directory.$firstFile;

        return $path;
    }

    /**
     * @param JobExecution $jobExecution
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
     */
    public function getBaseDirectory($jobType)
    {
        return $this->rootDir.DIRECTORY_SEPARATOR.$jobType.DIRECTORY_SEPARATOR;
    }
}

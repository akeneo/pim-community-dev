<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class Scheduler implements SchedulerInterface
{
    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var string */
    protected $checkStatus;

    /** @var string Source directory for files to schedule */
    protected $sourceDirectory;

    /** @var string Target directory for files to schedule */
    protected $scheduleDirectory;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param UploadCheckerInterface      $uploadChecker
     * @param RawFileStorerInterface $rawFileStorer
     */
    public function __construct(
        UploadCheckerInterface $uploadChecker,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->uploadChecker      = $uploadChecker;
        $this->rawFileStorer = $rawFileStorer;
    }

    /**
     * @param string $sourceDirectory
     */
    public function setSourceDirectory($sourceDirectory)
    {
        $this->sourceDirectory = $sourceDirectory;
    }

    /**
     * @param string $scheduleDirectory
     */
    public function setScheduleDirectory($scheduleDirectory)
    {
        $this->scheduleDirectory = $scheduleDirectory;
    }

    /**
     * {@inheritdoc}
     *
     * - check uploaded files
     * - Move files from tmp uploaded storage to tmp scheduled storage
     */
    public function schedule()
    {
        $files      = [];
        $fileSystem = new Filesystem();

        $storedFiles = array_diff(scandir($this->getSourceDir()), ['.', '..']);

        foreach ($storedFiles as $file) {
            $result = [
                'file'  => $file,
                'error' => null,
            ];
            if (!$this->isValidScheduledFilename($storedFiles, $file)) {
                $result['error'] = UploadStatus::STATUS_ERROR_CONFLICTS;
                $files[]         = $result;
                continue;
            }
            $filepath = $this->getSourceDir() . DIRECTORY_SEPARATOR . $file;
            $newPath  = $this->getScheduleDir() . DIRECTORY_SEPARATOR . $file;
            if (!is_dir(dirname($newPath))) {
                $fileSystem->mkdir(dirname($newPath));
            }
            $fileSystem->rename($filepath, $newPath);
            $files[] = $result;
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduledFiles()
    {
        $scheduleDir    = $this->getScheduleDir();
        $scheduledFiles = [];
        if (is_dir($scheduleDir)) {
            $scheduledFiles = array_diff(scandir($scheduleDir), ['.', '..']);
            $scheduledFiles = array_map(function ($filename) use ($scheduleDir) {
                return new \SplFileInfo($scheduleDir . DIRECTORY_SEPARATOR . $filename);
            }, $scheduledFiles);
        }

        return $scheduledFiles;
    }

    /**
     * @param string[] $storedFiles
     * @param string   $filenameToCheck
     *
     * @return bool
     */
    protected function isValidScheduledFilename($storedFiles, $filenameToCheck)
    {
        $valid = true;

        $otherFilenames = array_diff($storedFiles, [$filenameToCheck]);

        $checkedFilenameInfos = $this->uploadChecker->parseFilename($filenameToCheck);
        $checkedIsLocalized   = null !== $checkedFilenameInfos['locale'];

        foreach ($otherFilenames as $filename) {
            $comparedInfos       = $this->uploadChecker->parseFilename($filename);
            $comparedIsLocalized = null !== $comparedInfos['locale'];

            if ($checkedFilenameInfos['code'] === $comparedInfos['code']
                && $checkedIsLocalized !== $comparedIsLocalized
            ) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    /**
     * @return string
     */
    protected function getSourceDir()
    {
        return $this->sourceDirectory;
    }

    /**
     * @return string
     */
    protected function getScheduleDir()
    {
        return $this->scheduleDirectory;
    }
}

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

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class Scheduler implements SchedulerInterface
{
    /** @var UploaderInterface */
    protected $uploader;

    /** @var string */
    protected $checkStatus;

    /** @var string Source directory for files to schedule */
    protected $sourceDirectory;

    /** @var string Target directory for files to schedule */
    protected $scheduleDirectory;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param UploaderInterface        $uploader
     * @param RawFileStorerInterface   $rawFileStorer
     */
    public function __construct(
        UploaderInterface $uploader,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->uploader        = $uploader;
        $this->rawFileStorer   = $rawFileStorer;
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
     */
    public function schedule()
    {
        $files = [];

        $storedFiles = array_diff(scandir($this->getSourceDir()), ['.', '..']);

        foreach ($storedFiles as $file) {
            $result = [
                'file'  => $file,
                'error' => null,
            ];
            if (!$this->isValidScheduledFilename($storedFiles, $file)) {
                $result['error'] = UploadStatus::STATUS_ERROR_CONFLICTS;
                $files[]         = $result;
                var_dump('bar');
                continue;
            }
            $filepath = $this->getSourceDir() . DIRECTORY_SEPARATOR . $file;
            $newPath  = $this->getScheduleDir() . DIRECTORY_SEPARATOR . $file;
            if (!is_dir(dirname($newPath))) {
                mkdir(dirname($newPath), 0700, true);
            }
            rename($filepath, $newPath);
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
     * @param string   $checkedFilename
     *
     * @return bool
     */
    protected function isValidScheduledFilename($storedFiles, $checkedFilename)
    {
        $valid = true;

        $otherFilenames = array_diff($storedFiles, [$checkedFilename]);

        $checkedFilenameInfos = $this->uploader->parseFilename($checkedFilename);
        $checkedIsLocalized   = null !== $checkedFilenameInfos['locale'];

        foreach ($otherFilenames as $filename) {
            $comparedInfos       = $this->uploader->parseFilename($filename);
            $comparedIsLocalized = null !== $comparedInfos['locale'];

            if ($checkedFilenameInfos['code'] === $comparedInfos['code']
                && $checkedIsLocalized != $comparedIsLocalized
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

<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use League\Flysystem\Filesystem;

/**
 * Archive files written by job execution to provide them through a download button
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterArchiver extends AbstractFilesystemArchiver
{
    /** @var JobRegistry */
    protected $jobRegistry;

    /**
     * @param Filesystem  $filesystem
     * @param JobRegistry $jobRegistry
     */
    public function __construct(Filesystem $filesystem, JobRegistry $jobRegistry)
    {
        $this->filesystem = $filesystem;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();

            if ($this->isUsableWriter($writer)) {
                if ($writer instanceof ArchivableWriterInterface) {
                    $this->doArchive($jobExecution, $writer->getWrittenFiles());
                } else {
                    $this->doArchive($jobExecution, [$writer->getPath() => basename($writer->getPath())]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'output';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isUsableWriter($step->getWriter())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isUsableWriter(ItemWriterInterface $writer)
    {
        $isNewWriter = ($writer instanceof AbstractItemMediaWriter);
        $isNewItemMediaWriter = ($writer instanceof AbstractFileWriter);

        if (!($isNewItemMediaWriter || $isNewWriter)) {
            return false;
        }

        if ($writer instanceof ArchivableWriterInterface) {
            foreach ($writer->getWrittenFiles() as $filePath => $fileName) {
                if (!is_file($filePath)) {
                    return false;
                }
            }
            return true;
        }

        return is_file($writer->getPath());
    }

    /**
     * @param JobExecution $jobExecution
     * @param array        $filesToArchive ['filePath' => 'fileName']
     */
    protected function doArchive(JobExecution $jobExecution, array $filesToArchive)
    {
        foreach ($filesToArchive as $filePath => $fileName) {
            $archivedFilePath = strtr(
                $this->getRelativeArchivePath($jobExecution),
                [
                    '%filename%' => $fileName,
                ]
            );
            $this->filesystem->putStream($archivedFilePath, fopen($filePath, 'r'));
        }
    }
}

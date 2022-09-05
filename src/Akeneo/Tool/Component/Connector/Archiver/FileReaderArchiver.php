<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use League\Flysystem\FilesystemOperator;

/**
 * Archive files read by job execution to provide them through a download button
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver extends AbstractFilesystemArchiver
{
    protected JobRegistry $jobRegistry;

    public function __construct(FilesystemOperator $filesystem, JobRegistry $jobRegistry)
    {
        $this->filesystem = $filesystem;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution): void
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $reader = $step->getReader();

            if ($this->isReaderUsable($reader)) {
                $jobParameters = $jobExecution->getJobParameters();
                $filePath = $jobParameters->get('storage')['file_path'];

                $archivePath = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    [
                        '%filename%' => basename($filePath),
                    ]
                );

                if (is_readable($filePath)) {
                    $fileResource = fopen($filePath, 'r');
                    $this->filesystem->writeStream($archivePath, $fileResource);

                    if (is_resource($fileResource)) {
                        fclose($fileResource);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'input';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution): bool
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isReaderUsable($step->getReader())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if the reader is usable or not
     *
     * @param ItemReaderInterface $reader
     *
     * @return bool
     */
    protected function isReaderUsable(ItemReaderInterface $reader): bool
    {
        return $reader instanceof FileReaderInterface;
    }
}

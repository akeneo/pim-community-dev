<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use League\Flysystem\FilesystemOperator;

/**
 * Archive files read by job execution to provide them through a download button
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver extends AbstractFilesystemArchiver
{
    public function __construct(
        private readonly FilesystemOperator $localFilesystem,
        FilesystemOperator $archivistFilesystem,
        JobRegistry $jobRegistry,
    ) {
        parent::__construct($archivistFilesystem, $jobRegistry);
    }

    /**
     * {@inheritdoc}
     */
    public function archive(StepExecution $stepExecution): void
    {
        $step = $this->getStep($stepExecution);

        if (!$step instanceof ItemStep) {
            return;
        }

        $reader = $step->getReader();

        if ($this->isReaderUsable($reader)) {
            $jobParameters = $stepExecution->getJobExecution()->getJobParameters();
            $filePath = $jobParameters->get('storage')['file_path'];

            $archivePath = strtr(
                $this->getRelativeArchivePath($stepExecution->getJobExecution()),
                [
                    '%filename%' => basename($filePath),
                ]
            );

            if ($this->localFilesystem->fileExists($filePath)) {
                $fileResource = $this->localFilesystem->readStream($filePath);
                $this->archivistFilesystem->writeStream($archivePath, $fileResource);

                if (is_resource($fileResource)) {
                    fclose($fileResource);
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
    public function supports(StepExecution $stepExecution): bool
    {
        try {
            $step = $this->getStep($stepExecution);
        } catch (\Throwable) {
            return false;
        }

        return $step instanceof ItemStep && $this->isReaderUsable($step->getReader());
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

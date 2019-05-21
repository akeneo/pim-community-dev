<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use League\Flysystem\Filesystem;

/**
 * Archive files read by job execution to provide them through a download button
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver extends AbstractFilesystemArchiver
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
            $reader = $step->getReader();

            if ($this->isReaderUsable($reader)) {
                $jobParameters = $jobExecution->getJobParameters();
                $filePath = $jobParameters->get('filePath');
                $archivePath = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    [
                        '%filename%' => basename($filePath),
                    ]
                );

                if (is_readable($filePath)) {
                    $fileResource = fopen($filePath, 'r');
                    $this->filesystem->putStream($archivePath, $fileResource);

                    if (is_resource($fileResource)) {
                        fclose($fileResource);
                    }
                }
            }
        }
    }

    /**
     * Verify if the reader is usable or not
     *
     * @param ItemReaderInterface $reader
     *
     * @return bool
     */
    protected function isReaderUsable(ItemReaderInterface $reader)
    {
        return $reader instanceof Reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'input';
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution)
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isReaderUsable($step->getReader())) {
                return true;
            }
        }

        return false;
    }
}

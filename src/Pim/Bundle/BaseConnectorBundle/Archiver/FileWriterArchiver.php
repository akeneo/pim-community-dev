<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;

/**
 * Archive files written by job execution to provide them through a download button
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterArchiver extends AbstractFilesystemArchiver
{
    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {
        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();

            if ($this->isUsableWriter($writer)) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    [
                        '%filename%' => basename($writer->getPath()),
                    ]
                );
                $this->filesystem->put($key, file_get_contents($writer->getPath()));
            }
        }
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
        return $writer instanceof FileWriter && is_file($writer->getPath());
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
        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isUsableWriter($step->getWriter())) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Pim\Bundle\BaseConnectorBundle\Writer\File\ArchivableWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;

/**
 * Archive job execution files into conventional directories
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

            if ($writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1) {
                continue;
            }

            if ($writer instanceof FileWriter && is_file($path = $writer->getPath())) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    array(
                        '%filename%' => basename($writer->getPath()),
                    )
                );
                $this->filesystem->write($key, file_get_contents($path), true);
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
}

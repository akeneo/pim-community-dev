<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Reader\File\FileReader;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver extends AbstractFilesystemArchiver
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
            $reader = $step->getReader();

            if ($reader instanceof FileReader) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    array(
                        '%filename%' => basename($reader->getFilePath()),
                    )
                );
                $this->filesystem->write($key, file_get_contents($reader->getFilePath()), true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'input';
    }
}

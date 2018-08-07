<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\Filesystem;

/**
 * Base archiver
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFilesystemArchiver implements ArchiverInterface
{
    /** @var Filesystem */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    public function getArchives(JobExecution $jobExecution)
    {
        $directory = dirname($this->getRelativeArchivePath($jobExecution));
        $archives = [];

        foreach ($this->filesystem->listFiles($directory) as $key) {
            $archives[basename($key['path'])] = $key['path'];
        }

        return $archives;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(JobExecution $jobExecution, $key)
    {
        $archives = $this->getArchives($jobExecution);

        if (!isset($archives[$key])) {
            throw new \InvalidArgumentException(
                sprintf('Key "%s" does not exist', $key)
            );
        }

        return $this->filesystem->readStream($archives[$key]);
    }

    /**
     * Get the relative archive path in the file system
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    protected function getRelativeArchivePath(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();

        return
            $jobInstance->getType() . DIRECTORY_SEPARATOR .
            $jobInstance->getJobName() . DIRECTORY_SEPARATOR .
            $jobExecution->getId() . DIRECTORY_SEPARATOR .
            $this->getName() . DIRECTORY_SEPARATOR .
            '%filename%';
    }
}

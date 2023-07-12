<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;

/**
 * Base archiver
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFilesystemArchiver implements ArchiverInterface
{
    protected FilesystemOperator $filesystem;

    /**
     * {@inheritdoc}
     */
    public function getArchives(JobExecution $jobExecution, bool $deep = false): iterable
    {
        if (!$this->supportJobExecution($jobExecution)) {
            return [];
        }

        $directory = dirname($this->getRelativeArchivePath($jobExecution));
        $listing = $this->filesystem->listContents($directory, $deep)
            ->filter(fn (StorageAttributes $attributes): bool => $attributes->isFile())
            ->map(fn (StorageAttributes $attributes): string => $attributes->path());

        foreach ($listing as $path) {
            yield \ltrim(\substr($path, \strlen($directory)), '\\/') => $path;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(JobExecution $jobExecution, string $key)
    {
        $archives = $this->getArchives($jobExecution, true);
        foreach ($archives as $filename => $filepath) {
            if ($filename === $key) {
                return $this->filesystem->readStream($filepath);
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Key "%s" does not exist', $key)
        );
    }

    /**
     * Get the relative archive path in the file system
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    protected function getRelativeArchivePath(JobExecution $jobExecution): string
    {
        return $this->getArchiveDirectoryPath($jobExecution) . DIRECTORY_SEPARATOR . '%filename%';
    }

    public function getArchiveDirectoryPath(JobExecution $jobExecution): string
    {
        $jobInstance = $jobExecution->getJobInstance();

        return
            $jobInstance->getType() . DIRECTORY_SEPARATOR .
            $jobInstance->getJobName() . DIRECTORY_SEPARATOR .
            $jobExecution->getId() . DIRECTORY_SEPARATOR .
            $this->getName();
    }

    private function supportJobExecution(JobExecution $jobExecution): bool
    {
        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            if ($this->supports($stepExecution)) {
                return true;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;

/**
 * Base archiver
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFilesystemArchiver implements ArchiverInterface
{
    public function __construct(
        protected readonly FilesystemOperator $archivistFilesystem,
        protected readonly JobRegistry $jobRegistry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getArchives(JobExecution $jobExecution, bool $deep = false): iterable
    {
        if (!$this->supportsJobExecution($jobExecution)) {
            return [];
        }

        $directory = dirname($this->getRelativeArchivePath($jobExecution));
        $listing = $this->archivistFilesystem->listContents($directory, $deep)
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
                return $this->archivistFilesystem->readStream($filepath);
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

    private function supportsJobExecution(JobExecution $jobExecution): bool
    {
        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            if ($this->supports($stepExecution)) {
                return true;
            }
        }

        return false;
    }

    protected function getStep(StepExecution $stepExecution): StepInterface
    {
        $job = $this->getJob($stepExecution);

        $filteredSteps = array_values(array_filter($job->getSteps(), static fn (StepInterface $step) => $step->getName() === $stepExecution->getStepName()));

        if (0 === count($filteredSteps)) {
            throw new \RuntimeException('No step found corresponding to step execution.');
        }

        if (1 < count($filteredSteps)) {
            throw new \RuntimeException('Unable to distinguish the step. There is 2 or more steps with the same name in the job.');
        }

        return $filteredSteps[0];
    }

    protected function getJob(StepExecution $stepExecution): Job
    {
        $job = $this->jobRegistry->get($stepExecution->getJobExecution()->getJobInstance()->getJobName());

        if (!$job instanceof Job) {
            throw new \RuntimeException('Unable to fetch steps of the job.');
        }

        return $job;
    }
}

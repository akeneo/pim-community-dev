<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\FixtureLoader;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Installer\Domain\FixtureLoader\JobInstanceBuilderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader;
use Symfony\Component\Config\FileLocator;

class JobInstancesBuilder implements JobInstanceBuilderInterface
{
    public function __construct(
        private readonly FileLocator $fileLocator,
        private readonly Reader $yamlReader,
        private readonly ItemProcessorInterface $jobInstanceProcessor,
        private readonly array $jobsFilePaths
    ) {}

    /**
     * Load the fixture jobs in database
     *
     * @return JobInstance[]
     */
    public function build(): array
    {
        return $this->buildJobInstances($this->readOrderedRawJobData());
    }

    /**
     * @return array
     */
    protected function readOrderedRawJobData(): array
    {
        $rawJobs = [];
        foreach ($this->jobsFilePaths as $jobsFilePath) {
            $realPath = $this->fileLocator->locate('@' . $jobsFilePath);
            $jobExecution = new JobExecution();
            $jobParameters = new JobParameters(['storage' => ['type' => LocalStorage::TYPE, 'file_path' => $realPath]]);
            $jobExecution->setJobParameters($jobParameters);
            $stepExecution = new StepExecution('reader', $jobExecution);
            $this->yamlReader->setStepExecution($stepExecution);

            while ($rawJob = $this->yamlReader->read()) {
                $rawJobs[] = $rawJob;
            }

            usort(
                $rawJobs,
                function ($item1, $item2) {
                    if ($item1['order'] === $item2['order']) {
                        return 0;
                    }

                    return ($item1['order'] < $item2['order']) ? -1 : 1;
                }
            );
        }

        return $rawJobs;
    }

    /**
     * @return JobInstance[]
     */
    protected function buildJobInstances(array $rawJobs): array
    {
        $jobInstances = [];
        foreach ($rawJobs as $rawJob) {
            unset($rawJob['order']);
            $jobInstance = $this->jobInstanceProcessor->process($rawJob);
            $jobInstances[] = $jobInstance;
        }

        return $jobInstances;
    }
}

<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\Yaml;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Installer\Domain\Query\Yaml\GetJobDefinitionInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader;
use Symfony\Component\Config\FileLocator;

final class GetJobDefinition implements GetJobDefinitionInterface
{
    public function __construct(
        private readonly FileLocator $fileLocator,
        private readonly Reader $yamlReader,
    ) {
    }

    /**
     * @return mixed[]
     *
     * @throws InvalidItemException
     */
    public function get(array $jobsFilePaths): array
    {
        $normalizedJobs = [];

        foreach ($jobsFilePaths as $jobsFilePath) {
            $jobExecution = new JobExecution();
            $jobParameters = new JobParameters([
                'storage' => [
                    'type' => LocalStorage::TYPE,
                    'file_path' => $this->fileLocator->locate('@'.$jobsFilePath),
                ],
            ]);
            $jobExecution->setJobParameters($jobParameters);
            $stepExecution = new StepExecution('reader', $jobExecution);
            $this->yamlReader->setStepExecution($stepExecution);

            while ($rawJob = $this->yamlReader->read()) {
                $normalizedJobs[] = $rawJob;
            }
        }

        usort(
            $normalizedJobs,
            fn ($item1, $item2) => $item1['order'] <=> $item2['order'],
        );

        return $normalizedJobs;
    }
}

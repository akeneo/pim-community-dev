<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader;
use Symfony\Component\Config\FileLocator;

/**
 * Read the 'fixture_jobs.yml' to build the job instances that can be used to install the PIM
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobInstancesBuilder
{
    /** @var FileLocator */
    protected $fileLocator;

    /** @var Reader */
    protected $yamlReader;

    /** @var ItemProcessorInterface */
    protected $jobInstanceProcessor;

    /** @var array */
    protected $jobsFilePaths;

    /**
     * @param FileLocator            $locator
     * @param Reader                 $reader
     * @param ItemProcessorInterface $processor
     * @param array                  $jobsFilePaths
     */
    public function __construct(
        FileLocator $locator,
        Reader $reader,
        ItemProcessorInterface $processor,
        array $jobsFilePaths
    ) {
        $this->fileLocator = $locator;
        $this->yamlReader = $reader;
        $this->jobInstanceProcessor = $processor;
        $this->jobsFilePaths = $jobsFilePaths;
    }

    /**
     * Load the fixture jobs in database
     *
     * @return JobInstance[]
     */
    public function build()
    {
        $rawJobs = $this->readOrderedRawJobData();
        $jobInstances = $this->buildJobInstances($rawJobs);

        return $jobInstances;
    }

    /**
     * @return array
     */
    protected function readOrderedRawJobData()
    {
        $rawJobs = [];
        $fileLocator = $this->getFileLocator();
        foreach ($this->jobsFilePaths as $jobsFilePath) {
            $yamlReader = $this->getYamlReader();
            $realPath = $fileLocator->locate('@' . $jobsFilePath);
            $jobExecution = new JobExecution();
            $jobParameters = new JobParameters(['filePath' => $realPath]);
            $jobExecution->setJobParameters($jobParameters);
            $stepExecution = new StepExecution('reader', $jobExecution);
            $yamlReader->setStepExecution($stepExecution);

            while ($rawJob = $yamlReader->read()) {
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
     * @param array $rawJobs
     *
     * @return JobInstance[]
     */
    protected function buildJobInstances(array $rawJobs)
    {
        $processor = $this->getJobInstanceProcessor();
        $jobInstances = [];
        foreach ($rawJobs as $rawJob) {
            unset($rawJob['order']);
            $jobInstance = $processor->process($rawJob);
            $jobInstances[] = $jobInstance;
        }

        return $jobInstances;
    }

    /**
     * @return FileLocator
     */
    protected function getFileLocator()
    {
        return $this->fileLocator;
    }

    /**
     * @return YamlReader
     */
    protected function getYamlReader()
    {
        return $this->yamlReader;
    }

    /**
     * @return ItemProcessorInterface
     */
    protected function getJobInstanceProcessor()
    {
        return $this->jobInstanceProcessor;
    }
}

<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader;
use Pim\Component\Connector\Processor\Denormalization\SimpleProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load the jobs used to load fixtures
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FixtureJobLoader
{
    /** @staticvar */
    const JOB_TYPE = 'fixtures';

    /** @var string */
    protected $jobsFilePaths;

    /** @var FixturePathProvider */
    protected $pathProvider;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param FixturePathProvider $pathProvider
     * @param ContainerInterface  $container
     * @param array               $jobsFilePaths
     */
    public function __construct(
        FixturePathProvider $pathProvider,
        ContainerInterface $container,
        array $jobsFilePaths
    ) {
        $this->container = $container;
        $this->pathProvider = $pathProvider;
        $this->jobsFilePaths = $jobsFilePaths;
    }

    /**
     * Load the fixture jobs in database
     *
     * @param array $replacePaths
     */
    public function loadJobInstances(array $replacePaths = [])
    {
        $rawJobs = $this->readOrderedRawJobData();
        $jobInstances = $this->buildJobInstances($rawJobs);
        $configuredJobInstances = $this->configureJobInstances($jobInstances, $replacePaths);
        $saver = $this->getJobInstanceSaver();
        $saver->saveAll($configuredJobInstances);
    }

    /**
     * Deletes all the fixtures job
     */
    public function deleteJobInstances()
    {
        $jobInstances = $this->getJobInstanceRepository()->findBy(['type' => static::JOB_TYPE]);
        $remover = $this->getJobInstanceRemover();
        $remover->removeAll($jobInstances);
    }

    /**
     * Get the list of stored jobs
     *
     * @return JobInstance[]
     */
    public function getLoadedJobInstances()
    {
        $jobs = $this->getJobInstanceRepository()->findBy(['type' => self::JOB_TYPE]);

        return $jobs;
    }

    /**
     * @param JobInstance[] $jobInstances
     * @param array         $replacePaths
     *
     * @throws \Exception
     *
     * @return JobInstance[]
     */
    protected function configureJobInstances(array $jobInstances, array $replacePaths)
    {
        $configuredJobInstances = [];
        if (0 === count($replacePaths)) {
            $installerDataPath = $this->pathProvider->getFixturesPath();
            if (!is_dir($installerDataPath)) {
                throw new \Exception(sprintf('Path "%s" not found', $installerDataPath));
            }
            foreach ($jobInstances as $jobInstance) {
                $configuration = $jobInstance->getRawConfiguration();
                $configuration['filePath'] = sprintf('%s%s', $installerDataPath, $configuration['filePath']);
                if (!is_readable($configuration['filePath'])) {
                    throw new \Exception(
                        sprintf(
                            'The job "%s" can\'t be processed because the file "%s" is not readable',
                            $jobInstance->getCode(),
                            $configuration['filePath']
                        )
                    );
                }

                $jobInstance->setRawConfiguration($configuration);
                $configuredJobInstances[] = $jobInstance;
            }

            return $configuredJobInstances;

        } else {
            $counter = 0;
            foreach ($jobInstances as $jobInstance) {
                $configuration = $jobInstance->getRawConfiguration();
                if (!isset($replacePaths[$configuration['filePath']])) {
                    throw new \Exception(sprintf('No replacement path for "%s"', $configuration['filePath']));
                }
                foreach ($replacePaths[$configuration['filePath']] as $replacePath) {
                    $configuredJobInstance = clone $jobInstance;
                    $configuredJobInstance->setCode($configuredJobInstance->getCode().''.$counter++);
                    $configuration['filePath'] = $replacePath;
                    if (!is_readable($configuration['filePath'])) {
                        throw new \Exception(
                            sprintf(
                                'The job "%s" can\'t be processed because the file "%s" is not readable',
                                $configuredJobInstance->getCode(),
                                $configuration['filePath']
                            )
                        );
                    }
                    $configuredJobInstance->setRawConfiguration($configuration);
                    $configuredJobInstances[] = $configuredJobInstance;
                }
            }

            return $configuredJobInstances;
        }
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
     * @return array
     */
    protected function readOrderedRawJobData()
    {
        $rawJobs = [];
        $fileLocator = $this->container->get('file_locator');
        foreach ($this->jobsFilePaths as $jobsFilePath) {
            $yamlReader = $this->getYamlReader();
            $realPath = $fileLocator->locate('@' . $jobsFilePath);
            $yamlReader->setFilePath($realPath);

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
     * @return YamlReader
     */
    protected function getYamlReader()
    {
        return $this->container->get('pim_base_connector.reader.file.yaml');
    }

    /**
     * @return SimpleProcessor
     */
    protected function getJobInstanceProcessor()
    {
        return $this->container->get('pim_base_connector.processor.job_instance');
    }

    /**
     * @return BulkSaverInterface
     */
    protected function getJobInstanceSaver()
    {
        return $this->container->get('akeneo_batch.saver.job_instance');
    }

    /**
     * @return BulkRemoverInterface
     */
    protected function getJobInstanceRemover()
    {
        return $this->container->get('akeneo_batch.remover.job_instance');
    }

    /**
     * @return ObjectRepository
     */
    protected function getJobInstanceRepository()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em->getRepository($this->container->getParameter('akeneo_batch.entity.job_instance.class'));
    }
}

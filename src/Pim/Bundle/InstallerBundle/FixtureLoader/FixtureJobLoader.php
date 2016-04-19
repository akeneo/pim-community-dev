<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Bundle\BatchBundle\Validator\Constraints\JobInstance;
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

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param array              $jobsFilePaths
     * @throws \Exception
     */
    public function __construct(ContainerInterface $container, array $jobsFilePaths)
    {
        $this->container = $container;
        $this->jobsFilePaths = $jobsFilePaths;
    }

    /**
     * Load the fixture jobs in database
     *
     * TODO: refactor / split this class
     */
    public function load(array $replacePaths = [])
    {
        if (0 === count($replacePaths)) {
            $installerDataPath = $this->getInstallerDataPath();
            if (!is_dir($installerDataPath)) {
                throw new \Exception(sprintf('Path "%s" not found', $installerDataPath));
            }
        }

        // read the job instances from yaml files (can be CE + EE)
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

        // build the job instances
        $processor = $this->getJobInstanceProcessor();
        $jobInstances = [];
        foreach ($rawJobs as $rawJob) {
            unset($rawJob['order']);
            $jobInstance = $processor->process($rawJob);
            $config = $jobInstance->getRawConfiguration();

            if (0 === count($replacePaths)) {
                $config['filePath'] = sprintf('%s%s', $installerDataPath, $config['filePath']);
            } else {
                $replaced = false;
                foreach ($replacePaths as $replacePath) {
                    if (false !== strpos($replacePath, $config['filePath'])) {
                        $config['filePath'] = $replacePath;
                        $replaced = true;
                        break;
                    }
                }

                // TODO: product.csv can be empty for instance, in the case of Behat catalog
                //       we should enforce the fact that all files are presents
                if (false === $replaced) {
                    throw new \Exception(sprintf('No replacement path for "%s"', $config['filePath']));
                }
            }

            $jobInstance->setRawConfiguration($config);
            $jobInstances[] = $jobInstance;
        }

        // save the job instances
        $saver = $this->getJobInstanceSaver();
        $saver->saveAll($jobInstances);
    }

    /**
     * Deletes all the fixtures job
     */
    public function deleteJobs()
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
    public function getRunnableJobInstances()
    {
        $jobs = $this->getJobInstanceRepository()->findBy(['type' => self::JOB_TYPE]);
        foreach ($jobs as $index => $job) {
            // Do not load job when fixtures file is not available
            if (!is_readable($job->getRawConfiguration()['filePath'])) {
                unset($jobs[$index]);
            }
        }

        return $jobs;
    }

    /**
     * Get the path of the data used by the installer
     *
     * @return string
     */
    protected function getInstallerDataPath()
    {
        $installerDataDir = null;
        $installerData = $this->container->getParameter('installer_data');

        if (preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $installerData, $matches)) {
            $bundles = $this->container->getParameter('kernel.bundles');
            $reflection = new \ReflectionClass($bundles[$matches['bundle']]);
            $installerDataDir = dirname($reflection->getFilename()) . '/Resources/fixtures/' . $matches['directory'];
        } else {
            $installerDataDir = $this->container->getParameter('installer_data');
        }

        if (null === $installerDataDir || !is_dir($installerDataDir)) {
            throw new \RuntimeException('Installer data directory cannot be found.');
        }

        if (DIRECTORY_SEPARATOR !== substr($installerDataDir, -1, 1)) {
            $installerDataDir .= DIRECTORY_SEPARATOR;
        }

        return $installerDataDir;
    }

    /**
     * @return YamlReader
     */
    protected function getYamlReader()
    {
        return $this->container->get('pim_installer.reader.file.yaml');
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

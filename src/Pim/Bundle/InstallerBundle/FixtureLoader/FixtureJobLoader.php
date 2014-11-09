<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor;
use Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader;
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

    /** @var array */
    protected $jobsFilePaths;

    /** @var string */
    protected $installerDataPath;

    /** @var TransformerProcessor */
    protected $processor;

    /** @var YamlReader */
    protected $reader;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var ContainerInterface  */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param array              $jobsFilePaths
     *
     * @throws \Exception
     */
    public function __construct(ContainerInterface $container, array $jobsFilePaths)
    {
        $this->container = $container;
        $this->reader = $container->get('pim_base_connector.reader.file.yaml');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->processor = $container->get('pim_base_connector.processor.job_instance');
        $this->jobsFilePaths = $jobsFilePaths;

        $this->installerDataPath = $this->getInstallerDataPath();
        if (!is_dir($this->installerDataPath)) {
            throw new \Exception(sprintf('Path "%s" not found', $this->installerDataPath));
        }
    }

    /**
     * Load the fixture jobs in database
     *
     * @throws \Exception
     */
    public function load()
    {
        $rawJobs = $this->readJobsData();
        $this->loadJobs($rawJobs);
    }

    /**
     * Deletes all the fixtures job
     */
    public function deleteJobs()
    {
        $jobs = $this->em->getRepository($this->container->getParameter('akeneo_batch.entity.job_instance.class'))
                ->findBy(array('type' => FixtureJobLoader::JOB_TYPE));

        foreach ($jobs as $job) {
            $this->em->remove($job);
        }

        $this->em->flush();
    }

    /**
     * Reads jobs data
     *
     * @return array
     */
    protected function readJobsData()
    {
        $rawJobs = array();
        $fileLocator = $this->container->get('file_locator');

        foreach ($this->jobsFilePaths as $jobsFilePath) {

            $realPath = $fileLocator->locate('@'.$jobsFilePath);
            $this->reader->setFilePath($realPath);

            // read the jobs list
            while ($rawJob = $this->reader->read()) {
                $rawJobs[] = $rawJob;
            }

            // sort the jobs by order
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
     * Saves jobs
     *
     * @param array $rawJobs
     *
     * @throws \Exception
     */
    protected function loadJobs(array $rawJobs)
    {
        $filePaths = [];
        foreach ($rawJobs as $rawJob) {
            unset($rawJob['order']);
            $job = $this->processor->process($rawJob);
            $config = $job->getRawConfiguration();
            $config['filePath'] = sprintf('%s/%s', $this->installerDataPath, $config['filePath']);
            $filePaths[$rawJob['code']] = $config['filePath'];
            $job->setRawConfiguration($config);
            $this->em->persist($job);
        }

        $optionalCodes = [
            'fixtures_category_csv',
            'fixtures_category_yml',
            'fixtures_product_csv',
            'fixtures_product_yml'
        ];
        foreach ($filePaths as $code => $filePath) {
            if (!in_array($code, $optionalCodes) && !file_exists($filePath)) {
                throw new \Exception(
                    sprintf(
                        'The fixture file "%s" is not found, this data set is not complete',
                        $filePath
                    )
                );
            }
        }

        if (!file_exists($filePaths['fixtures_category_csv']) && !file_exists($filePaths['fixtures_category_yml'])) {
            throw new \Exception(
                sprintf(
                    'A fixture file "%s" or "%s" is expected, this data set is not complete',
                    $filePaths['fixtures_category_csv'],
                    $filePaths['fixtures_category_yml']
                )
            );
        }

        if (!file_exists($filePaths['fixtures_product_csv']) && !file_exists($filePaths['fixtures_product_yml'])) {
            throw new \Exception(
                sprintf(
                    'A fixture file "%s" or "%s" is expected, this data set is not complete',
                    $filePaths['fixtures_product_csv'],
                    $filePaths['fixtures_product_yml']
                )
            );
        }

        $this->em->flush();
    }

    /**
     * Get the path of the data used by the installer
     *
     * @return string
     */
    protected function getInstallerDataPath()
    {
        $installerData = $this->container->getParameter('installer_data');
        preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $installerData, $matches);
        $bundles    = $this->container->getParameter('kernel.bundles');
        $reflection = new \ReflectionClass($bundles[$matches['bundle']]);

        return dirname($reflection->getFilename()) . '/Resources/fixtures/' . $matches['directory'];
    }
}

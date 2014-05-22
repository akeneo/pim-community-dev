<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\DBAL\Connection;
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
    const JOB_INSTANCE_TABLE = 'akeneo_batch_job_instance';

    /** @staticvar */
    const JOB_TYPE = 'fixtures';

    /** @var string */
    protected $jobsFilePath;

    /** @var string */
    protected $installerDataPath;

    /** @var TransformerProcessor */
    protected $processor;

    /** @var YamlReader */
    protected $reader;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var Connection */
    protected $connection;

    /** @var ContainerInterface  */
    protected $container;

    public function __construct(ContainerInterface $container, $jobsFilePath)
    {
        $this->container = $container;
        $this->reader = $container->get('pim_base_connector.reader.file.yaml');
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->connection = $container->get('database_connection');
        $this->processor = $container->get('pim_base_connector.processor.job_instance');
        $this->installerDataPath = $this->getInstallerDataPath();
        $this->jobsFilePath = $jobsFilePath;
    }

    /**
     * Load the fixture jobs in database
     */
    public function load()
    {
        $rawJobs = array();
        $this->reader->setFilePath($this->jobsFilePath);

        // read the jobs list
        while ($rawJob = $this->reader->read()) {
            $rawJobs[] = $rawJob;
        }

        // sort the jobs by order
        usort($rawJobs, function($item1, $item2) {
            if ($item1['order'] === $item2['order']) {

                return 0;
            }

            return ($item1['order'] < $item2['order']) ? -1 : 1;
        });

        // store the jobs
        foreach ($rawJobs as $rawJob) {
            unset($rawJob['order']);
            $job = $this->processor->process($rawJob);
            $config = $job->getRawConfiguration();
            $config['filePath'] = sprintf('%s/%s', $this->installerDataPath, $config['filePath']);
            $job->setRawConfiguration($config);

            $this->em->persist($job);
        }

        $this->em->flush();
    }

    /**
     * Deletes all the fixtures job
     */
    public function deleteJobs()
    {
        //TODO: use repository with class name as parameters
        $this->connection->delete(self::JOB_INSTANCE_TABLE, array('type' => self::JOB_TYPE));
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

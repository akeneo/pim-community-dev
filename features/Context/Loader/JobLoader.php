<?php

namespace Context\Loader;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\InstallerBundle\DataFixtures\ORM\AbstractInstallerFixture;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Loader for jobs
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobLoader extends AbstractInstallerFixture
{
    /**
     * @var string Path of the fixtures file
     */
    protected $filePath;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['jobs'])) {
            foreach ($configuration['jobs'] as $code => $data) {
                $job = $this->createJob($code, $data);
                $manager->persist($job);
            }
        }

        $manager->flush();
    }

    /**
     * @param string $filePath
     *
     * @return JobLoader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
    }

    /**
     * @param string $code
     * @param array  $data
     *
     * @return JobInstance
     */
    protected function createJob($code, array $data)
    {
        $job = new JobInstance($data['connector'], $data['type'], $data['alias']);
        $job->setCode($code);
        $job->setLabel($data['label']);
        $job->setType($data['type']);
        $job->setRawConfiguration($this->prepareConfiguration($data));

        return $job;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareConfiguration(array $data)
    {
        $title = sprintf('pim_import_export.jobs.%s.%s.title', $data['alias'], $data['type']);

        $config = array(
            'reader'    => isset($data['configuration']['reader']) ? $data['configuration']['reader'] : array(),
            'processor' => isset($data['configuration']['processor']) ? $data['configuration']['processor'] : array(),
            'writer'    => isset($data['configuration']['writer']) ? $data['configuration']['writer'] : array(),
        );

        return array($title => $config);
    }
}

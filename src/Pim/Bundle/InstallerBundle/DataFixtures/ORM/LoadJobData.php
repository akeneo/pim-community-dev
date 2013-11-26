<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Load fixtures for jobs
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadJobData extends AbstractInstallerFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['jobs'])) {
            foreach ($configuration['jobs'] as $code => $data) {
                $job = $this->createJob($code, $data);
                $this->validate($job, $data);
                $manager->persist($job);
            }

            $manager->flush();
        }
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
        $job->setRawConfiguration($this->prepareConfiguration($data['alias'], $data['steps']));

        return $job;
    }

    /**
     * @param string $alias
     * @param array  $steps
     *
     * @return array
     */
    protected function prepareConfiguration($alias, array $steps)
    {
        $configuration = array();
        foreach ($steps as $step => $data) {
            $title = sprintf('pim_import_export.jobs.%s.%s.title', $alias, $step);
            $config = array(
                'reader'    => isset($data['reader']) ? $data['reader'] : array(),
                'processor' => isset($data['processor']) ? $data['processor'] : array(),
                'writer'    => isset($data['writer']) ? $data['writer'] : array(),
            );
            $configuration[$title]= $config;
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'jobs';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 160;
    }
}
